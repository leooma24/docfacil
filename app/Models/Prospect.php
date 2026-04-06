<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Prospect extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'clinic_name', 'city',
        'specialty', 'source', 'status', 'notes',
        'contacted_at', 'converted_at', 'address',
    ];

    protected function casts(): array
    {
        return [
            'contacted_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function lifecycleEmails(): MorphMany
    {
        return $this->morphMany(LifecycleEmail::class, 'emailable');
    }
}
