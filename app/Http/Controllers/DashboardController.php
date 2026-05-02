<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->get('current_property');

        $roomQuery      = Room::when($property, fn($q) => $q->where('property_id', $property->id));
        $totalRooms     = (clone $roomQuery)->count();
        $occupiedRooms  = (clone $roomQuery)->where('status', 'occupied')->count();
        $availableRooms = (clone $roomQuery)->where('status', 'available')->count();
        $occupancyRate  = $totalRooms > 0 ? round($occupiedRooms / $totalRooms * 100) : 0;

        $invoiceQuery    = Invoice::when($property, fn($q) => $q->where('property_id', $property->id));
        $pendingInvoices = (clone $invoiceQuery)->whereIn('status', ['pending', 'sent'])->count();
        $overdueInvoices = (clone $invoiceQuery)->where('status', 'overdue')->count();

        $monthlyIncome = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->when($property, fn($q) => $q->whereHas('invoice', fn($qi) => $qi->where('property_id', $property->id)))
            ->sum('amount');

        $recentInvoices = (clone $invoiceQuery)
            ->with(['rental.room', 'rental.tenant'])
            ->whereIn('status', ['pending', 'sent', 'overdue'])
            ->orderBy('due_date')
            ->limit(8)
            ->get();

        $pendingMaintenance = MaintenanceRequest::with('room')
            ->when($property, fn($q) => $q->where('property_id', $property->id))
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'totalRooms', 'occupiedRooms', 'availableRooms', 'occupancyRate',
            'pendingInvoices', 'overdueInvoices', 'monthlyIncome',
            'recentInvoices', 'pendingMaintenance'
        ));
    }
}
