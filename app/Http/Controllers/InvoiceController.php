<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MeterReading;
use App\Models\Room;
use App\Services\InvoiceService;
use App\Services\PlatformRevenueService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(private InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $property = $request->get('current_property');

        $query = Invoice::with(['rental.room', 'rental.tenant'])
            ->when($property, fn ($q) => $q->where('property_id', $property->id));

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('month'))  $query->where('month', $request->month);
        if ($request->filled('year'))   $query->where('year', $request->year);

        $invoices = $query->orderByDesc('year')->orderByDesc('month')->orderBy('invoice_number')->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    public function generateForm(Request $request)
    {
        $property = $request->get('current_property');
        $month    = $request->integer('month', now()->month);
        $year     = $request->integer('year', now()->year);

        $roomIds = Room::when($property, fn ($q) => $q->where('property_id', $property->id))->pluck('id');

        $readingsCount = MeterReading::where('month', $month)->where('year', $year)
            ->whereIn('room_id', $roomIds)->count();
        $existingCount = Invoice::where('month', $month)->where('year', $year)
            ->when($property, fn ($q) => $q->where('property_id', $property->id))->count();

        return view('invoices.generate', compact('month', 'year', 'readingsCount', 'existingCount'));
    }

    public function generate(Request $request)
    {
        $property = $request->get('current_property');

        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020',
        ]);

        $result    = $this->invoiceService->generateBulk($request->month, $request->year, $property?->id);
        $generated = count($result['generated']);
        $skipped   = count($result['skipped']);

        return redirect()->route('invoices.index', ['month' => $request->month, 'year' => $request->year])
            ->with('success', "สร้างใบแจ้งหนี้สำเร็จ {$generated} ใบ" . ($skipped ? " (ข้าม {$skipped} ห้อง)" : ''));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['rental.room.roomType', 'rental.tenant', 'items', 'payments']);
        return view('invoices.show', compact('invoice'));
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['rental.room.roomType', 'rental.tenant', 'items', 'property']);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))->setPaper('a4', 'portrait');
        return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_date'     => 'required|date',
            'payment_method'   => 'required|in:cash,transfer,other',
            'reference_number' => 'nullable|string|max:100',
            'note'             => 'nullable|string',
        ]);

        $payment = $invoice->payments()->create([
            'amount'           => $invoice->total_amount,
            'payment_date'     => $request->payment_date,
            'payment_method'   => $request->payment_method,
            'reference_number' => $request->reference_number,
            'note'             => $request->note,
        ]);

        $invoice->update(['status' => 'paid', 'paid_at' => $request->payment_date]);

        app(PlatformRevenueService::class)->recordManualPayment($invoice->fresh('property'), $payment);

        return redirect()->route('invoices.show', $invoice)->with('success', 'บันทึกการชำระเงินสำเร็จ');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'ไม่สามารถลบใบแจ้งหนี้ที่ชำระแล้ว');
        }
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'ลบใบแจ้งหนี้สำเร็จ');
    }
}
