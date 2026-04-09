<?php

namespace App\Filament\Doctor\Pages;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (session()->has('demo_credentials')) {
            $creds = session('demo_credentials');
            $this->form->fill([
                'email' => $creds['email'] ?? '',
                'password' => $creds['password'] ?? '',
                'remember' => false,
            ]);
        }
    }
}
