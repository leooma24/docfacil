<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Toda clase que usan las vistas del panel del doctor existe en algún CSS
 * que el panel carga.
 *
 * El panel solo cargaba el CSS ya compilado de Filament, que no trae los
 * colores de Tailwind: "Habilitar 2FA", "Agendar", "Felicitar", "Enviar
 * comprobante" y otros salían con letra blanca sobre blanco, y lo rojo, ámbar
 * y verde salía gris (auditoría del 12-oct-2026). Ahora
 * public/css/panel-doctor.css trae las que faltan; si alguien usa una clase
 * nueva y no corre `npm run css:panel`, esta prueba avisa.
 */
class ClasesQueSiExistenTest extends TestCase
{
    use RefreshDatabase;

    private const VISTAS = [
        'resources/views/filament/doctor',
        'resources/views/filament/custom',
        'resources/views/livewire',
        'resources/views/components',
    ];

    /** Marcas sin estilo propio en el panel: el odontograma las comparte con la landing y sus pruebas. */
    private const MARCAS = ['odo-arcadas', 'arcada', 'odo-diente', 'odo-caras'];

    private const CSS = [
        'public/css/filament/filament/app.css',
        'public/css/filament/forms/forms.css',
        'public/css/filament/support/support.css',
        'public/css/panel-doctor.css',
    ];

    public function test_el_panel_carga_el_css_de_sus_pantallas(): void
    {
        $clinica = Clinic::create(['name' => 'Consultorio', 'plan' => 'profesional', 'plan_ends_at' => now()->addMonth(), 'onboarding_status' => 'completed']);
        $user = User::forceCreate(['name' => 'Dr. Test', 'email' => 'd@test.com', 'password' => bcrypt('x'), 'role' => 'doctor', 'email_verified_at' => now(), 'clinic_id' => $clinica->id]);
        Doctor::create(['user_id' => $user->id, 'clinic_id' => $clinica->id, 'specialty' => 'Odontología']);

        $this->actingAs($user)->get('/doctor')->assertOk()->assertSee('css/panel-doctor.css', false);
    }

    /**
     * Con el celular en modo oscuro, Caja, Recordatorios, Pendientes, Su mes y
     * Corte salían con nombres y montos blancos sobre tarjetas blancas: esas
     * pantallas no están hechas para modo oscuro. Apagado hasta que lo estén.
     */
    public function test_el_panel_del_doctor_va_en_modo_claro(): void
    {
        $this->assertFalse(\Filament\Facades\Filament::getPanel('doctor')->hasDarkMode());
    }

    public function test_cada_clase_de_las_vistas_existe_en_algun_css(): void
    {
        $vistas = collect(self::VISTAS)
            ->flatMap(fn ($dir) => \Illuminate\Support\Facades\File::allFiles(base_path($dir)))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.blade.php'));

        // Lo que definen el CSS y los <style> de las propias vistas.
        $definido = collect(self::CSS)->map(fn ($f) => file_get_contents(base_path($f)))->implode("\n")
            . $vistas->map(fn ($f) => implode("\n", $this->estilos($f->getContents())))->implode("\n");
        $existen = [];
        preg_match_all('/\.((?:\\\\.|[A-Za-z0-9_-])+)/', $definido, $m);
        foreach ($m[1] as $clase) {
            $existen[stripslashes($clase)] = true;
        }

        $faltan = [];
        foreach ($vistas as $f) {
            // class="…" de verdad; no :class ni x-bind:class de Alpine, que son JavaScript.
            preg_match_all('/(?<![:\w-])class="([^"]*)"/', $f->getContents(), $atributos);
            foreach ($atributos[1] as $valor) {
                // Lo dinámico ({{ }}, @if, $variables) no se puede revisar aquí.
                foreach (preg_split('/\s+/', preg_replace('/\{\{.*?\}\}|\{!!.*?!!\}|@\w+(\([^)]*\))?/', ' ', $valor)) as $clase) {
                    // Las que se arman con una variable ("pp-sigue-{{ $x }}") tampoco.
                    if ($clase === '' || str_ends_with($clase, '-') || str_contains($clase, '::') || preg_match('/[{}$()\'"@]/', $clase)
                        || isset($existen[$clase]) || in_array($clase, self::MARCAS, true)) {
                        continue;
                    }
                    $faltan[] = str_replace(base_path() . '/', '', $f->getPathname()) . ' → ' . $clase;
                }
            }
        }

        $faltan = array_values(array_unique($faltan));
        $this->assertSame([], $faltan, count($faltan) . " clases que ningún CSS trae (¿falta `npm run css:panel`?):\n" . implode("\n", array_slice($faltan, 0, 80)));
    }

    /** @return list<string> el contenido de cada <style> de la vista */
    private function estilos(string $vista): array
    {
        preg_match_all('/<style\b[^>]*>(.*?)<\/style>/s', $vista, $m);

        return $m[1];
    }
}
