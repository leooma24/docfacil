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
use Illuminate\Support\Str;

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
        // Base humanizada (mantiene branding) + 6 chars random (alfanumérico).
        // Espacio: ~57 mil millones de combinaciones por base — no enumerable.
        $base = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name ?? 'DOC'), 0, 4));

        do {
            $code = $base . strtoupper(Str::random(6));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    private static function generateSalesRepCode(?string $name): string
    {
        $base = 'VND-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name ?? 'VENTAS'), 0, 4));

        do {
            $code = $base . strtoupper(Str::random(6));
        } while (self::where('sales_rep_code', $code)->exists());

        return $code;
    }

    // ─────────────────────────────────────────────────────────────
    //  Nombre para mostrar
    //
    //  En México la mayoría de los doctores se registran escribiendo
    //  su título ("Dr. Roberto García"). Si la interfaz antepone otro
    //  "Dr." queda "Dr. Dr. Roberto". Estos helpers respetan el título
    //  que el doctor ya escribió y no inventan uno cuando no lo puso
    //  (así nunca le decimos "Dr." a una doctora).
    // ─────────────────────────────────────────────────────────────

    /** Títulos que un doctor puede haber escrito al inicio de su nombre. */
    private const TITULOS = ['dr', 'dra', 'doctor', 'doctora', 'c.d', 'cd', 'mc', 'esp'];

    /**
     * Separa el título del resto del nombre.
     *
     * @return array{titulo: string, resto: string}
     */
    private function separarTitulo(): array
    {
        $nombre = trim((string) $this->name);
        if ($nombre === '') {
            return ['titulo' => '', 'resto' => ''];
        }

        $partes = preg_split('/\s+/', $nombre);
        $primera = rtrim(mb_strtolower($partes[0]), '.');

        if (in_array($primera, self::TITULOS, true) && count($partes) > 1) {
            return [
                'titulo' => $partes[0],
                'resto' => implode(' ', array_slice($partes, 1)),
            ];
        }

        return ['titulo' => '', 'resto' => $nombre];
    }

    /**
     * Nombre completo listo para mostrar, sin duplicar el título.
     * "Dr. Roberto García" → "Dr. Roberto García"
     * "Roberto García"     → "Roberto García"
     */
    public function displayName(): string
    {
        return trim((string) $this->name) ?: 'Doctor';
    }

    /**
     * Nombre corto para saludar, respetando el título escrito.
     * "Dr. Roberto García"  → "Dr. Roberto"
     * "Dra. Ana López Ruiz" → "Dra. Ana"
     * "Roberto García"      → "Roberto"
     */
    public function shortDisplayName(): string
    {
        ['titulo' => $titulo, 'resto' => $resto] = $this->separarTitulo();

        if ($resto === '') {
            return $titulo ?: 'Doctor';
        }

        $primerNombre = preg_split('/\s+/', $resto)[0];

        return trim($titulo . ' ' . $primerNombre);
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
