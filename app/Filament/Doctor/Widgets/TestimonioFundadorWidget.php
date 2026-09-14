<?php

namespace App\Filament\Doctor\Widgets;

use App\Mail\TestimonioDeFundadorMail;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * A los 30 días le pide al fundador una frase sobre DocFácil, y permiso para
 * publicarla.
 *
 * A los anuncios les faltan testimonios y no hay de dónde sacarlos: inventar
 * uno está prohibido (art. 32 de la Ley Federal de Protección al Consumidor).
 * Así el programa Fundador va dejando frases reales, con nombre y con permiso
 * por escrito. La frase le llega a Omar, que decide si la publica.
 */
class TestimonioFundadorWidget extends Widget
{
    protected static string $view = 'filament.doctor.widgets.testimonio-fundador';

    protected int|string|array $columnSpan = 'full';

    // Debajo del saludo (-10) y del checklist de arranque (-8).
    protected static ?int $sort = -7;

    public string $frase = '';

    public string $firma = '';

    // Nunca viene marcado de fábrica: un permiso que el doctor no tocó no es
    // permiso.
    public bool $permiso = false;

    public bool $enviado = false;

    public bool $oculto = false;

    public static function canView(): bool
    {
        $usuario = auth()->user();

        return $usuario?->role === 'doctor'
            && (bool) $usuario->clinic?->tocaPedirTestimonio();
    }

    public function mount(): void
    {
        $usuario = auth()->user();

        // Se le propone la firma ya armada; si la quiere distinta, la cambia.
        $this->firma = collect([$usuario->name, $usuario->clinic?->name, $usuario->clinic?->city])
            ->filter()
            ->implode(' · ');
    }

    public function enviar(): void
    {
        $datos = $this->validate(
            [
                'frase' => ['required', 'string', 'min:15', 'max:600'],
                'firma' => ['required', 'string', 'max:120'],
                'permiso' => ['boolean'],
            ],
            [
                'frase.required' => 'Escribe tu frase.',
                'frase.min' => 'Cuéntame un poco más: con una o dos oraciones basta.',
                'frase.max' => 'Que sea corta: máximo 600 letras.',
                'firma.required' => 'Dime cómo quieres que aparezca tu nombre.',
                'firma.max' => 'El nombre va muy largo: máximo 120 letras.',
            ],
        );

        $clinica = auth()->user()->clinic;

        $clinica->update([
            'case_study_testimonial' => trim($datos['frase']),
            'testimonio_firma' => trim($datos['firma']),
            'testimonio_permiso_at' => $datos['permiso'] ? now() : null,
        ]);

        try {
            Mail::to(config('founders.avisar_a'))->send(new TestimonioDeFundadorMail($clinica));
        } catch (\Throwable $e) {
            // La frase ya quedó guardada y se ve en el admin. Que falle el
            // aviso no es motivo para decirle al doctor que algo salió mal.
            Log::warning('No se pudo avisar de la frase de un fundador', [
                'clinic_id' => $clinica->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->enviado = true;
    }

    /** "Ahora no": se le vuelve a preguntar en dos semanas. */
    public function despues(): void
    {
        auth()->user()->clinic->update(['testimonio_pospuesto_hasta' => now()->addDays(14)]);

        $this->oculto = true;
    }

    /** "No volver a preguntar": no se le vuelve a preguntar. */
    public function noPreguntar(): void
    {
        auth()->user()->clinic->update(['testimonio_descartado_at' => now()]);

        $this->oculto = true;
    }
}
