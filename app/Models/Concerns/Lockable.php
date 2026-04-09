<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Trait de inmutabilidad — NOM-004-SSA3-2012.
 *
 * Un modelo con esta trait queda bloqueado 24h después de creado.
 * Los updates y deletes posteriores lanzan excepción. Las correcciones
 * se hacen creando un nuevo registro (addendum) que referencia al original.
 */
trait Lockable
{
    protected static function bootLockable(): void
    {
        static::updating(function ($model) {
            if ($model->isLocked() && !$model->isDirty('locked_at')) {
                throw new \LogicException(
                    'Este registro está bloqueado por normativa NOM-004 ' .
                    '(expediente clínico inmutable). Crea un addendum en su lugar.'
                );
            }
        });

        static::deleting(function ($model) {
            if ($model->isLocked()) {
                throw new \LogicException(
                    'Este registro está bloqueado por normativa NOM-004 ' .
                    'y debe conservarse al menos 5 años.'
                );
            }
        });
    }

    public function isLocked(): bool
    {
        if ($this->locked_at !== null) {
            return true;
        }
        // Auto-lock tras 24 horas de creado
        return $this->created_at && $this->created_at->diffInHours(now()) >= 24;
    }

    public function lock(): void
    {
        if ($this->locked_at === null) {
            $this->forceFill(['locked_at' => now()])->saveQuietly();
        }
    }
}
