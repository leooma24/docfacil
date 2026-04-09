<?php

namespace App\Providers;

use App\Models\Clinic;
use App\Observers\ClinicObserver;
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
    }
}
