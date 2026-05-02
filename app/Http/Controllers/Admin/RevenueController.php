<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformRevenue;
use App\Models\Property;
use Illuminate\Http\Request;

class RevenueController extends Controller
{
    public function index(Request $request)
    {
        $revenues = PlatformRevenue::with(['property', 'omiseTransaction', 'invoice.rental.room'])
            ->latest()
            ->paginate(30);

        $totalFee      = PlatformRevenue::sum('fee_amount');
        $pendingPayout = PlatformRevenue::where('status', 'pending')->sum('net_amount');
        $ownerReceivable = PlatformRevenue::where('status', 'unpaid')->sum('fee_amount');
        $properties    = Property::withCount('rooms')->get();

        return view('admin.revenues.index', compact('revenues', 'totalFee', 'pendingPayout', 'ownerReceivable', 'properties'));
    }

    public function show(PlatformRevenue $revenue)
    {
        $revenue->load(['property', 'omiseTransaction.invoice.rental.tenant']);
        return view('admin.revenues.show', compact('revenue'));
    }
}
