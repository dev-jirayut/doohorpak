<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(): View
    {
        $properties = Property::withCount('rooms')->with('users')->orderBy('name')->paginate(12);
        return view('admin.properties.index', compact('properties'));
    }

    public function create(): View
    {
        return view('admin.properties.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'type'    => 'required|in:dormitory,hotel',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
        ]);

        Property::create($request->only('name', 'type', 'address', 'phone'));

        return redirect()->route('admin.properties.index')->with('success', 'เพิ่มที่พักสำเร็จ');
    }

    public function edit(Property $property): View
    {
        $allUsers = User::orderBy('name')->get();
        $assignedIds = $property->users()->pluck('users.id')->toArray();
        return view('admin.properties.edit', compact('property', 'allUsers', 'assignedIds'));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'type'    => 'required|in:dormitory,hotel',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
        ]);

        $property->update($request->only('name', 'type', 'address', 'phone') + [
            'is_active' => $request->boolean('is_active'),
        ]);

        // sync users
        $property->users()->sync($request->input('user_ids', []));

        return redirect()->route('admin.properties.index')->with('success', 'แก้ไขที่พักสำเร็จ');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $property->delete();
        return redirect()->route('admin.properties.index')->with('success', 'ลบที่พักสำเร็จ');
    }
}
