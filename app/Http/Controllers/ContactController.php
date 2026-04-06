<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot: if this hidden field is filled, it's a bot
        if ($request->filled('website_url')) {
            return back()->with('contact_success', true);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'clinic_name' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        Prospect::updateOrCreate(
            ['email' => $validated['email']],
            [
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'clinic_name' => $validated['clinic_name'] ?? null,
                'specialty' => $validated['specialty'] ?? null,
                'city' => $validated['city'] ?? null,
                'notes' => $validated['message'] ?? null,
                'source' => 'landing',
                'status' => 'new',
            ]
        );

        return back()->with('contact_success', true);
    }
}
