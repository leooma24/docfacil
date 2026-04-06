<?php

namespace App\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;

class SignaturePad extends Component
{
    #[Locked]
    public ?int $consentFormId = null;
    public ?string $signatureData = null;
    public bool $signed = false;

    public function mount(?int $consentFormId = null): void
    {
        $this->consentFormId = $consentFormId;
    }

    public function saveSignature(string $imageData): void
    {
        if (!$this->consentFormId || empty($imageData)) {
            return;
        }

        $consent = \App\Models\ConsentForm::where('clinic_id', auth()->user()->clinic_id)
            ->find($this->consentFormId);

        if (!$consent) {
            return;
        }

        // Decode base64 and save as file
        $image = str_replace('data:image/png;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        $fileName = 'signatures/firma-' . $consent->id . '-' . time() . '.png';

        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, base64_decode($image));

        $consent->update([
            'signature' => $fileName,
            'signed_at' => now(),
            'signed_ip' => request()->ip(),
        ]);

        $this->signed = true;
        $this->dispatch('signature-saved');
    }

    public function render()
    {
        return view('livewire.signature-pad');
    }
}
