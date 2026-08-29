<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Observers\AppointmentObserver;
use App\Observers\ClinicObserver;
use Filament\Facades\Filament;
use Filament\Notifications\Auth\ResetPassword;
use Filament\Notifications\Auth\VerifyEmail;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // En producción, todas las URLs generadas (incluyendo signed routes,
        // emails, redirects) usan https. Defensa-en-profundidad sobre el
        // proxy Apache/nginx que ya hace 80→443. Previene cookies en HTTP
        // si por alguna razón llega un request por puerto 80.
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Clinic::observe(ClinicObserver::class);
        Appointment::observe(AppointmentObserver::class);

        $this->personalizarCorreosDeAcceso();

        $bccAll = array_filter(array_map(
            'trim',
            explode(',', (string) env('MAIL_BCC_ALL', ''))
        ));

        if (!empty($bccAll)) {
            Event::listen(function (MessageSending $event) use ($bccAll) {
                // Skip BCC si el mailable lo pidio explicitamente. Lo usamos
                // para los correos del pipeline de prospects (3-10 al dia)
                // que llenan el inbox del admin sin aportar valor.
                $headers = $event->message->getHeaders();
                if ($headers->has('X-DocFacil-Skip-Bcc')) {
                    $headers->remove('X-DocFacil-Skip-Bcc');
                    return;
                }
                foreach ($bccAll as $addr) {
                    $event->message->addBcc($addr);
                }
            });
        }
    }

    /**
     * Correos de verificacion y de restablecer contraseña con nuestra marca.
     *
     * Laravel manda su plantilla por defecto: en ingles ("Hello! Please click
     * the button below...") y sin colores ni logo. En un producto que le habla
     * de tu a un dentista mexicano eso se ve como phishing, y es justo el
     * primer correo que recibe una cuenta nueva.
     */
    private function personalizarCorreosDeAcceso(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage())
                ->subject('Confirma tu correo para entrar a DocFácil')
                ->view('emails.verificar-correo', [
                    'nombre' => $this->primerNombre($notifiable),
                    'url' => $url,
                ]);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $panel = Filament::getCurrentPanel();

            return (new MailMessage())
                ->subject('Restablece tu contraseña de DocFácil')
                ->view('emails.restablecer-contrasena', [
                    'nombre' => $this->primerNombre($notifiable),
                    'url' => $panel?->getResetPasswordUrl($token, $notifiable) ?? url('/doctor/login'),
                    'minutos' => config('auth.passwords.users.expire', 60),
                ]);
        });
    }

    /**
     * Nombre de pila para saludar, respetando el titulo que el doctor escribio.
     * Devuelve vacio cuando no hay nombre util, para no saludar "Hola Doctor".
     */
    private function primerNombre(object $notifiable): string
    {
        if (! method_exists($notifiable, 'shortDisplayName')) {
            return '';
        }

        $corto = $notifiable->shortDisplayName();

        return $corto === 'Doctor' ? '' : $corto;
    }
}
