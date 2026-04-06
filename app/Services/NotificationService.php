<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Notification;

class NotificationService
{
    public static function appointmentCompleted(User $doctor, string $patientName): void
    {
        Notification::make()
            ->title('Consulta completada')
            ->body("Expediente de {$patientName} guardado correctamente.")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->sendToDatabase($doctor);
    }

    public static function newPatientRegistered(User $doctor, string $patientName): void
    {
        Notification::make()
            ->title('Nuevo paciente registrado')
            ->body("{$patientName} ha sido agregado a tu consultorio.")
            ->icon('heroicon-o-user-plus')
            ->iconColor('info')
            ->sendToDatabase($doctor);
    }

    public static function paymentReceived(User $doctor, string $patientName, float $amount): void
    {
        Notification::make()
            ->title('Pago recibido')
            ->body("\${$amount} de {$patientName}")
            ->icon('heroicon-o-banknotes')
            ->iconColor('success')
            ->sendToDatabase($doctor);
    }

    public static function trialExpiringSoon(User $doctor, int $daysLeft): void
    {
        Notification::make()
            ->title("Tu prueba gratuita vence en {$daysLeft} días")
            ->body('Actualiza tu plan para seguir usando todas las funciones.')
            ->icon('heroicon-o-clock')
            ->iconColor('warning')
            ->sendToDatabase($doctor);
    }

    public static function appointmentReminder(User $doctor, string $patientName, string $time): void
    {
        Notification::make()
            ->title("Próxima cita: {$patientName}")
            ->body("Programada a las {$time}")
            ->icon('heroicon-o-calendar')
            ->iconColor('primary')
            ->sendToDatabase($doctor);
    }
}
