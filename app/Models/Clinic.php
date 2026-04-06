<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Clinic extends Model
{
    protected $fillable = [
        'name', 'slug', 'phone', 'email', 'address',
        'city', 'state', 'zip_code', 'logo', 'plan',
        'trial_ends_at', 'is_active',
        'is_beta', 'is_founder', 'founder_price',
        'beta_starts_at', 'beta_ends_at', 'beta_notes',
        'show_as_case_study', 'case_study_logo', 'case_study_testimonial',
        'onboarding_status',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'beta_starts_at' => 'datetime',
            'beta_ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_beta' => 'boolean',
            'is_founder' => 'boolean',
            'show_as_case_study' => 'boolean',
            'founder_price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Clinic $clinic) {
            if (empty($clinic->slug)) {
                $clinic->slug = Str::slug($clinic->name);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
