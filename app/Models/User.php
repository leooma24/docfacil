<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'clinic_id',
        'referral_code',
    ];

    protected $guarded = ['role'];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code) && in_array($user->role, ['doctor', null])) {
                $user->referral_code = self::generateReferralCode($user->name);
            }
        });
    }

    private static function generateReferralCode(?string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name ?? 'DOC'), 0, 6));
        $code = $base . rand(100, 999);

        while (self::where('referral_code', $code)->exists()) {
            $code = $base . rand(100, 999);
        }

        return $code;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'terms_accepted_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled && $this->two_factor_confirmed_at !== null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->role === 'super_admin',
            'doctor' => in_array($this->role, ['doctor', 'staff']),
            'paciente' => $this->role === 'patient',
            default => false,
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function clinic(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }
}
