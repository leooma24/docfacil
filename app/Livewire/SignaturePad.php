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

        // Validar formato base64 PNG/JPEG (anti-arbitrary-file-upload)
        if (! preg_match('/^data:image\/(png|jpeg);base64,/i', $imageData, $matches)) {
            $this->dispatch('signature-error', message: 'Formato inválido');
            return;
        }
        $ext = strtolower($matches[1]) === 'jpeg' ? 'jpg' : 'png';

        $payload = preg_replace('/^data:image\/(png|jpeg);base64,/i', '', $imageData);
        $payload = str_replace(' ', '+', $payload);
        $binary = base64_decode($payload, true);

        // Sanity: tamaño máximo 2 MB (suficiente para firma manuscrita)
        if (! $binary || strlen($binary) > 2 * 1024 * 1024) {
            $this->dispatch('signature-error', message: 'Firma demasiado grande');
            return;
        }

        // Disk PRIVADO (storage/app/private/signatures) — no public.
        // Servida por SignedRoute con verificación de clinic_id en SignatureController.
        $fileName = 'signatures/firma-' . $consent->id . '-' . time() . '.' . $ext;
        \Illuminate\Support\Facades\Storage::disk('local')->put($fileName, $binary);

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
