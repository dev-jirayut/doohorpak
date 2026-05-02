<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::withCount('rooms')->orderBy('name')->paginate(15);
        return view('room-types.index', compact('roomTypes'));
    }

    public function create()
    {
        return view('room-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'base_price'  => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        RoomType::create($request->only('name', 'base_price', 'description'));

        return redirect()->route('room-types.index')
            ->with('success', 'เพิ่มประเภทห้องสำเร็จ');
    }

    public function edit(RoomType $roomType)
    {
        return view('room-types.edit', compact('roomType'));
    }

    public function update(Request $request, RoomType $roomType)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'base_price'  => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $roomType->update($request->only('name', 'base_price', 'description'));

        return redirect()->route('room-types.index')
            ->with('success', 'แก้ไขประเภทห้องสำเร็จ');
    }

    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->exists()) {
            return back()->with('error', 'ไม่สามารถลบได้เนื่องจากมีห้องพักใช้ประเภทนี้อยู่');
        }

        $roomType->delete();
        return redirect()->route('room-types.index')
            ->with('success', 'ลบประเภทห้องสำเร็จ');
    }
}
