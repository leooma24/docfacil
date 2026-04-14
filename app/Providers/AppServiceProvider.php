<?php

namespace App\Providers;

use App\Models\Clinic;
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

        $bccAll = array_filter(array_map(
            'trim',
            explode(',', (string) env('MAIL_BCC_ALL', ''))
        ));

        if (!empty($bccAll)) {
            Event::listen(function (MessageSending $event) use ($bccAll) {
                foreach ($bccAll as $addr) {
                    $event->message->addBcc($addr);
                }
            });
        }
    }
}
