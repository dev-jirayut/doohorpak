<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertySwitchController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        if ($request->user()->isSuperAdmin() && $request->input('property_id') === 'all') {
            session(['current_property_id' => 'all']);
            return redirect()->route('dashboard');
        }

        $property = Property::findOrFail($request->property_id);

        if (!$request->user()->canAccessProperty($property->id)) {
            abort(403);
        }

        session(['current_property_id' => $property->id]);

        return redirect()->route('dashboard');
    }
}
