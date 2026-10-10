<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * El panel del doctor le habla de usted, en todas sus pantallas.
 *
 * Los 20 doctores simulados del 12-oct-2026 lo notaron todos: unas pantallas
 * decían "te deben", "Puedes ajustarlo", "Agenda una nueva cita" y otras "Le
 * recordamos". Mezclado se lee descuidado. Esta prueba lee los textos que ve
 * el doctor (cadenas de PHP y texto de las vistas, sin comentarios ni código)
 * y avisa de cualquier forma de tú.
 */
class PanelDeUstedTest extends TestCase
{
    /** Pronombres y verbos que solo existen en tú. */
    private const DE_TU = ['tú', 'tu', 'tus', 'te', 'ti', 'contigo', 'puedes', 'tienes', 'quieres', 'necesitas', 'debes', 'estás',
        'eres', 'sabes', 'vas', 'haces', 'usas', 'podrás', 'tendrás', 'recibirás', 'verás', 'llevas', 'ayudamos a que'];

    /** Órdenes de tú al empezar una frase (las de usted terminan distinto: "Registre", "Agende"). */
    private const ORDENES = ['Registra', 'Envía', 'Invita', 'Anota', 'Elige', 'Escribe', 'Selecciona', 'Agrega', 'Revisa', 'Configura',
        'Sube', 'Usa', 'Llena', 'Captura', 'Personaliza', 'Activa', 'Descarga', 'Comparte', 'Pega', 'Toca', 'Abre', 'Cambia',
        'Completa', 'Crea', 'Busca', 'Imprime', 'Copia', 'Edita', 'Confirma', 'Cobra', 'Asigna', 'Define', 'Ingresa', 'Haz', 'Dale',
        'Prueba', 'Empieza', 'Deja', 'Manda', 'Avisa', 'Mira', 'Pídele', 'Escríbele', 'Mándale', 'Recuérdale', 'Sigue', 'Descubre',
        'Aprovecha', 'Conoce', 'Ahorra', 'Olvídate', 'Agenda una', 'Agenda la', 'Agenda tu', 'Agenda su'];

    /**
     * Textos que la prueba marca y no son de tú: "Prueba hasta" es la prueba
     * gratis (sustantivo) y "Sigue igual" es el botón que dice que el dato
     * del paciente no cambió.
     */
    private const NO_SON_DE_TU = ['Prueba hasta', 'Sigue igual'];

    /**
     * Omar tiene cambios sin guardar en este archivo en su copia (12-oct-2026):
     * no se toca desde aquí para no chocar. Le queda un "Anota el número del
     * manifiesto"; al guardar sus cambios se pasa a "Anote" y se quita de aquí.
     */
    private const SIN_TOCAR = ['app/Filament/Doctor/Resources/HazardousWasteResource.php'];

    private function archivos(): array
    {
        $globs = [
            'app/Filament/Doctor/*.php', 'app/Filament/Doctor/*/*.php', 'app/Filament/Doctor/*/*/*.php', 'app/Filament/Doctor/*/*/*/*.php',
            'resources/views/filament/doctor/*.blade.php', 'resources/views/filament/doctor/*/*.blade.php',
            'resources/views/filament/doctor/*/*/*.blade.php', 'resources/views/filament/doctor/*/*/*/*.blade.php',
            // Lo que se pinta en todas las páginas del panel: el botón de ayuda y el asistente.
            'resources/views/filament/custom/*.blade.php', 'app/Livewire/AssistantChat.php', 'resources/views/livewire/assistant-chat.blade.php',
        ];

        return collect($globs)->flatMap(fn ($g) => glob(base_path($g)))->unique()->values()->all();
    }

    /** Los textos que ve el doctor en ese archivo. */
    private function textos(string $archivo): array
    {
        $src = file_get_contents($archivo);

        if (str_ends_with($archivo, '.blade.php')) {
            $src = preg_replace(['/\{\{--.*?--\}\}/s', '/<script\b.*?<\/script>/s', '/<style\b.*?<\/style>/s', '/@php.*?@endphp/s', '/\{\{.*?\}\}/s', '/\{!!.*?!!\}/s', '/<[^>]+>/s', '/@\w+(\s*\([^)]*\))?/'], "\n", $src);

            return preg_split('/\n+/', $src);
        }

        $textos = [];
        foreach (token_get_all($src) as $t) {
            if (is_array($t) && in_array($t[0], [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)) {
                $textos[] = trim($t[1], "'\"");
            }
        }

        return $textos;
    }

    public function test_el_panel_del_doctor_habla_de_usted(): void
    {
        $tu = '/(?<![\p{L}\-\.\/@_])(' . implode('|', self::DE_TU) . ')(?![\p{L}\-\.\/_])/iu';
        $orden = '/(?:^|[\.\!\?:]\s+|^\s*[\-•·]\s*)(' . implode('|', self::ORDENES) . ')(?=\s)/u';
        $fuera = [];

        foreach ($this->archivos() as $archivo) {
            if (in_array(str_replace(base_path() . '/', '', $archivo), self::SIN_TOCAR, true)) {
                continue;
            }
            foreach ($this->textos($archivo) as $texto) {
                $texto = trim($texto);
                // Claves, rutas, clases CSS y similares: sin espacios no son frases.
                if ($texto === '' || ! str_contains($texto, ' ') || in_array($texto, self::NO_SON_DE_TU, true)) {
                    continue;
                }
                if (preg_match($tu, $texto, $m) || preg_match($orden, $texto, $m)) {
                    $fuera[] = str_replace(base_path() . '/', '', $archivo) . ' → «' . mb_substr($texto, 0, 90) . '» (' . $m[1] . ')';
                }
            }
        }

        $this->assertSame([], $fuera, count($fuera) . " textos de tú:\n" . implode("\n", $fuera));
    }
}
