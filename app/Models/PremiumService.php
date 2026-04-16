<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Catálogo de servicios premium que DocFácil vende a las clínicas como addon:
 * setup, capacitación, branding, WhatsApp API setup, plantillas legales, campañas.
 *
 * Nota de nombres: el modelo `Service` existente es para los servicios médicos
 * del consultorio (tratamientos/consultas que el doctor vende a sus pacientes).
 * Este `PremiumService` es distinto — son los servicios que DocFácil vende al doctor.
 */
class PremiumService extends Model
{
    use LogsActivity;

    public const CATEGORIES = [
        'setup' => 'Setup e instalación',
        'capacitacion' => 'Capacitación',
        'branding' => 'Branding y diseño',
        'whatsapp' => 'WhatsApp y mensajería',
        'legal' => 'Legal y consentimientos',
        'marketing' => 'Marketing y campañas',
    ];

    public const PRICING_TYPES = [
        'one_time' => 'Pago único',
        'monthly' => 'Mensualidad recurrente',
        'custom_quote' => 'Cotización personalizada',
    ];

    public const TARGET_AUDIENCE = [
        'all' => 'Todos los consultorios',
        'dental' => 'Solo dental',
        'medico' => 'Solo médico general/especialidad',
        'clinica' => 'Solo clínica multi-doctor',
    ];

    protected $fillable = [
        'slug', 'name', 'category',
        'price_mxn', 'pricing_type', 'sla_days',
        'short_desc', 'long_desc', 'bullets',
        'icon_svg_path', 'target_audience',
        'is_active', 'is_featured', 'sort_order',
        'requires_intake', 'intake_form_schema',
        'seller_commission_pct',
    ];

    protected function casts(): array
    {
        return [
            'price_mxn' => 'decimal:2',
            'bullets' => 'array',
            'intake_form_schema' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'requires_intake' => 'boolean',
            'sla_days' => 'integer',
            'sort_order' => 'integer',
            'seller_commission_pct' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price_mxn', 'is_active'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $event) => "Servicio premium {$event}");
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(PremiumServicePurchase::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeForAudience($q, string $audience)
    {
        return $q->where(function ($sub) use ($audience) {
            $sub->where('target_audience', 'all')->orWhere('target_audience', $audience);
        });
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function pricingLabel(): string
    {
        return match ($this->pricing_type) {
            'one_time' => '$' . number_format($this->price_mxn, 0) . ' único',
            'monthly' => '$' . number_format($this->price_mxn, 0) . ' / mes',
            'custom_quote' => 'Precio a cotizar',
            default => '$' . number_format($this->price_mxn, 0),
        };
    }
}
