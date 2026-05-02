<?php

namespace App\Http\Middleware;

use App\Models\Property;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CurrentPropertyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // super_admin sees all; owner sees their own; staff sees assigned
        $properties = match (true) {
            $user->isSuperAdmin() => Property::where('is_active', true)->orderBy('name')->get(),
            $user->isOwner()      => $user->ownedProperties()->where('is_active', true)->orderBy('name')->get(),
            default               => $user->properties()->where('is_active', true)->orderBy('name')->get(),
        };

        if ($properties->isEmpty()) {
            // No properties yet — allow access so admin can create one
            view()->share('currentProperty', null);
            view()->share('userProperties', collect());
            return $next($request);
        }

        $currentId = session('current_property_id');

        if ($user->isSuperAdmin() && (!$currentId || $currentId === 'all')) {
            session(['current_property_id' => 'all']);
            view()->share('currentProperty', null);
            view()->share('userProperties', $properties);
            $request->merge(['current_property' => null]);

            return $next($request);
        }

        // Validate the stored property is still accessible
        $current = $properties->firstWhere('id', $currentId);
        if (!$current) {
            $current = $properties->first();
            session(['current_property_id' => $current->id]);
        }

        view()->share('currentProperty', $current);
        view()->share('userProperties', $properties);
        $request->merge(['current_property' => $current]);

        return $next($request);
    }
}
