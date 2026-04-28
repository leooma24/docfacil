<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Observers\AppointmentObserver;
use App\Observers\ClinicObserver;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Clinic::observe(ClinicObserver::class);
        Appointment::observe(AppointmentObserver::class);

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
}
