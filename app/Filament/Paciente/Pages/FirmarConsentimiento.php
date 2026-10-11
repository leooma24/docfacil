<?php

namespace App\Filament\Paciente\Pages;

use App\Models\ConsentForm;
use App\Models\Patient;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * El paciente lee y firma su consentimiento desde su celular, antes de la
 * cita (día 9 del plan, 10-oct-2026). La firma queda como en el consultorio:
 * imagen, fecha e IP.
 */
class FirmarConsentimiento extends Page
{
    protected static ?string $slug = 'firmar-consentimiento';

    protected static ?string $title = 'Firmar consentimiento';

    protected static string $view = 'filament.paciente.firmar-consentimiento';

    protected static bool $shouldRegisterNavigation = false;

    public ?int $consentimientoId = null;

    public function mount(): void
    {
        $this->consentimientoId = $this->consentimiento((int) request('consentimiento'))?->id ?? abort(404);
    }

    private function consentimiento(int $id): ?ConsentForm
    {
        $paciente = Patient::withoutGlobalScopes()->where('user_id', auth()->id())->first();

        return $paciente ? ConsentForm::withoutGlobalScopes()->where('clinic_id', $paciente->clinic_id)
            ->where('patient_id', $paciente->id)->find($id) : null;
    }

    public function firmar(string $firma): void
    {
        $c = $this->consentimiento((int) $this->consentimientoId);
        if (! $c || $c->signed_at) {
            return;
        }
        if (! preg_match('/^data:image\/png;base64,[A-Za-z0-9+\/=]+$/', $firma) || strlen($firma) > 400000) {
            Notification::make()->title('La firma no se pudo leer. Bórrela y vuelva a firmar.')->danger()->send();

            return;
        }

        $c->forceFill(['signature' => $firma, 'signed_at' => now(), 'signed_ip' => request()->ip()])->save();
        Notification::make()->title('Firmado. Gracias.')->success()->send();
    }

    protected function getViewData(): array
    {
        $c = $this->consentimiento((int) $this->consentimientoId);

        return [
            'consentimiento' => $c,
            'contenido' => strip_tags((string) $c?->content, '<p><br><ul><ol><li><strong><b><em><i><h3><h4>'),
        ];
    }
}
