<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('activeRental.room');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id_card', 'like', "%{$search}%");
            });
        }

        $tenants = $query->orderBy('name')->paginate(20);
        return view('tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('tenants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                      => 'required|string|max:150',
            'id_card'                   => 'nullable|string|max:20',
            'phone'                     => 'nullable|string|max:20',
            'email'                     => 'nullable|email|max:150',
            'address'                   => 'nullable|string',
            'emergency_contact_name'    => 'nullable|string|max:150',
            'emergency_contact_phone'   => 'nullable|string|max:20',
            'note'                      => 'nullable|string',
        ]);

        Tenant::create($request->all());

        return redirect()->route('tenants.index')
            ->with('success', 'เพิ่มผู้เช่าสำเร็จ');
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['rentals.room', 'activeRental.room']);
        return view('tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant)
    {
        return view('tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name'                      => 'required|string|max:150',
            'id_card'                   => 'nullable|string|max:20',
            'phone'                     => 'nullable|string|max:20',
            'email'                     => 'nullable|email|max:150',
            'address'                   => 'nullable|string',
            'emergency_contact_name'    => 'nullable|string|max:150',
            'emergency_contact_phone'   => 'nullable|string|max:20',
            'note'                      => 'nullable|string',
        ]);

        $tenant->update($request->all());

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'แก้ไขข้อมูลผู้เช่าสำเร็จ');
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->rentals()->exists()) {
            return back()->with('error', 'ไม่สามารถลบได้เนื่องจากมีประวัติการเช่าอยู่');
        }

        $tenant->delete();
        return redirect()->route('tenants.index')
            ->with('success', 'ลบผู้เช่าสำเร็จ');
    }
}
