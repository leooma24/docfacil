<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Los add-ons dicen lo que hacen.
 *
 * "Recall automático" y "Reseñas Google automatizadas" no mandan nada solos:
 * DocFácil arma la lista y, con un clic, abre el WhatsApp del doctor con el
 * mensaje listo; él da enviar. Igual la felicitación de cumpleaños: el
 * comando que la mandaría no está programado. Regla de Omar (octubre 2026):
 * no se promete lo que el sistema no hace.
 */
class AddOnsSinPrometerDeMasTest extends TestCase
{
    public function test_ningun_add_on_se_dice_automatico_ni_promete_cifras(): void
    {
        foreach (config('addons') as $slug => $addon) {
            $texto = $addon['name'] . ' ' . $addon['short_description'] . ' ' . $addon['long_description'];

            $this->assertDoesNotMatchRegularExpression('/autom[aá]tic|automatizad/iu', $texto, $slug);
            $this->assertDoesNotMatchRegularExpression('/triplic|\d+x\b|\d+%/iu', $texto, $slug);
        }
    }

    public function test_dicen_que_se_abre_su_whatsapp_y_el_da_enviar(): void
    {
        foreach (['recall_automation', 'google_reviews'] as $slug) {
            $this->assertStringContainsString('usted da enviar', config("addons.{$slug}.long_description"), $slug);
        }
    }

    public static function frasesQueYaNo(): array
    {
        return [
            'ajustes del consultorio' => ['app/Filament/Doctor/Pages/ClinicSettings.php', 'Reseñas Google automatizadas'],
            'bienvenida' => ['resources/views/filament/doctor/pages/onboarding.blade.php', 'reseñas automáticas'],
            'correo de bienvenida' => ['resources/views/emails/welcome-onboarding.blade.php', 'Recall automático'],
            'cumpleaños del paciente' => ['app/Filament/Doctor/Resources/PatientResource.php', 'cumpleaños automáticamente'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('frasesQueYaNo')]
    public function test_la_frase_ya_no_promete_de_mas(string $archivo, string $frase): void
    {
        $this->assertStringNotContainsString($frase, file_get_contents(base_path($archivo)));
    }
}
