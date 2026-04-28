<?php

namespace App\Filament\Doctor\Resources\DoctorInvitationResource\Pages;

use App\Filament\Doctor\Concerns\HasFormHero;
use App\Filament\Doctor\Resources\DoctorInvitationResource;
use App\Mail\DoctorInvitationMail;
use App\Models\DoctorInvitation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateDoctorInvitation extends CreateRecord
{
    use HasFormHero;

    protected static string $resource = DoctorInvitationResource::class;

    protected static string $view = 'filament.doctor.resources.create-with-hero';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['clinic_id'] = auth()->user()->clinic_id;
        $data['invited_by'] = auth()->id();

        return $data;
    }

    /**
     * Después de crear la invitación, mandar email al invitado con el link
     * /invitation/{token}. Si falla el envío, no rompemos la creación —
     * loggeamos y avisamos para que el usuario comparta el link manual.
     */
    protected function afterCreate(): void
    {
        /** @var DoctorInvitation $invitation */
        $invitation = $this->record;

        try {
            Mail::to($invitation->email)->send(new DoctorInvitationMail($invitation));
            Notification::make()
                ->title('Invitación enviada')
                ->body("Se mandó correo a {$invitation->email}. La invitación expira en 7 días.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Log::warning('DoctorInvitationMail failed', [
                'invitation_id' => $invitation->id,
                'email' => $invitation->email,
                'error' => $e->getMessage(),
            ]);
            Notification::make()
                ->title('Invitación creada, pero no se pudo enviar el correo')
                ->body("Comparte manualmente este link con {$invitation->name}: " . route('invitation.accept', ['token' => $invitation->token]))
                ->warning()
                ->persistent()
                ->send();
        }
    }

    protected function getFormHeroConfig(): array
    {
        return [
            'title'    => 'Invitar doctor',
            'icon'     => '👨‍⚕️',
            'kicker'   => '➕ Agregar al equipo',
            'subtitle' => 'Invita a un doctor a unirse a tu clínica. Recibirá un link para registrarse por email o WhatsApp.',
            'gradient' => '#ec4899 0%, #d946ef 40%, #a855f7 100%',
            'accent'   => '#ec4899',
        ];
    }
}
