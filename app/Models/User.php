<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'clinic_id',
        'referral_code',
        'commission_rate_percent',
        'is_active_sales_rep',
        'sales_rep_code',
    ];

    protected $guarded = ['role'];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code) && in_array($user->role, ['doctor', null])) {
                $user->referral_code = self::generateReferralCode($user->name);
            }

            if (empty($user->sales_rep_code) && $user->role === 'sales') {
                $user->sales_rep_code = self::generateSalesRepCode($user->name);
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

    private static function generateSalesRepCode(?string $name): string
    {
        $base = 'VND-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name ?? 'VENTAS'), 0, 5));
        $code = $base . rand(10, 99);

        while (self::where('sales_rep_code', $code)->exists()) {
            $code = $base . rand(10, 99);
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
            'commission_rate_percent' => 'decimal:2',
            'is_active_sales_rep' => 'boolean',
            'chatbot_autologin_expires_at' => 'datetime',
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
            'ventas' => $this->role === 'sales' && $this->is_active_sales_rep,
            default => false,
        };
    }

    /**
     * One-time-use token para auto-login despues de crear cuenta via chatbot.
     * Cualquier uso del metodo consume el token y limpia la columna.
     */
    public function generateChatbotAutologinToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->forceFill([
            'chatbot_autologin_token' => hash('sha256', $token),
            'chatbot_autologin_expires_at' => now()->addMinutes(15),
        ])->save();
        return $token;
    }

    public function consumeChatbotAutologinToken(string $token): bool
    {
        $hashed = hash('sha256', $token);
        if (
            !hash_equals((string) $this->chatbot_autologin_token, $hashed)
            || $this->chatbot_autologin_expires_at === null
            || $this->chatbot_autologin_expires_at->isPast()
        ) {
            return false;
        }
        $this->forceFill([
            'chatbot_autologin_token' => null,
            'chatbot_autologin_expires_at' => null,
        ])->save();
        return true;
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

    public function soldClinics(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Clinic::class, 'sold_by_user_id');
    }

    public function commissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function assignedProspects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Prospect::class, 'assigned_to_sales_rep_id');
    }

    public function scopeSalesReps($q)
    {
        return $q->where('role', 'sales');
    }

    public function isSalesRep(): bool
    {
        return $this->role === 'sales';
    }
}
