<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Patient;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function show(string $slug)
    {
        $clinic = Clinic::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('checkin.form', compact('clinic'));
    }

    public function store(Request $request, string $slug)
    {
        $clinic = Clinic::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergies' => 'nullable|string|max:500',
            'reason_for_visit' => 'nullable|string|max:500',
            'honeypot' => 'nullable|size:0',
        ]);

        if (!empty($data['honeypot'])) {
            return back();
        }

        // Avoid duplicates by phone
        $existing = null;
        if (!empty($data['phone'])) {
            $existing = Patient::where('clinic_id', $clinic->id)
                ->where('phone', $data['phone'])
                ->first();
        }

        if ($existing) {
            if (!empty($data['reason_for_visit'])) {
                $existing->update([
                    'medical_notes' => trim(($existing->medical_notes ?? '') . "\n[" . now()->format('d/m/Y H:i') . "] Motivo: " . $data['reason_for_visit']),
                ]);
            }
            return view('checkin.success', ['clinic' => $clinic, 'returning' => true]);
        }

        Patient::create([
            'clinic_id' => $clinic->id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'gender' => $data['gender'] ?? null,
            'blood_type' => $data['blood_type'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'medical_notes' => !empty($data['reason_for_visit'])
                ? "[" . now()->format('d/m/Y H:i') . "] Motivo: " . $data['reason_for_visit']
                : null,
            'is_active' => true,
        ]);

        return view('checkin.success', ['clinic' => $clinic, 'returning' => false]);
    }
}
