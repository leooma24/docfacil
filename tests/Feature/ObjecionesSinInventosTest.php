<?php

namespace Tests\Feature;

use App\Models\Prospect;
use Tests\TestCase;

/**
 * Lo que el vendedor lee cuando le ponen una objeción.
 *
 * El modal decía "Somos de aquí de Culiacán" —es Los Mochis, y es una persona,
 * no un equipo—, ofrecía pasar "el contacto de un doctor que ya lo usa" cuando
 * no hay ninguno, y prometía recuperar de 8 a 12 citas al mes por $4,800.
 * Ninguno de esos números sale de ningún lado.
 *
 * Una objeción se responde con lo que se puede sostener cuando el doctor
 * pregunta de dónde salió el dato.
 */
class ObjecionesSinInventosTest extends TestCase
{
    /**
     * Lo que se ve en pantalla, no el archivo.
     *
     * El comentario de arriba del blade cita a propósito las frases que se
     * quitaron, para que quede escrito por qué se fueron. Leyendo el archivo
     * crudo, esas citas darían falsos positivos: lo que importa es lo que el
     * vendedor alcanza a leer cuando abre el modal.
     */
    private function texto(): string
    {
        return view('filament.sales.objections-modal')->render();
    }


    public function test_las_17_objeciones_del_catalogo_tienen_respuesta(): void
    {
        $texto = $this->texto();

        foreach (array_keys(Prospect::OBJECTION_CATALOG) as $clave) {
            $this->assertStringContainsString(
                $clave,
                $texto,
                "Falta la respuesta para la objeción {$clave}, que sí se puede registrar en un prospecto.",
            );
        }
    }

    public function test_no_dice_que_es_de_culiacan(): void
    {
        $this->assertStringNotContainsString('Culiacán', $this->texto());
    }

    public function test_no_promete_clientes_que_todavia_no_existen(): void
    {
        $texto = strtolower($this->texto());

        $this->assertStringNotContainsString('ya tenemos consultorios usándolo', $texto);
        $this->assertStringNotContainsString('contacto de un doctor que ya lo usa', $texto);
    }

    public function test_no_trae_cifras_de_ahorro_inventadas(): void
    {
        $texto = $this->texto();

        foreach (['8 y 12 citas', '4,800', 'se paga casi 10 veces'] as $invento) {
            $this->assertStringNotContainsString($invento, $texto);
        }
    }
}
