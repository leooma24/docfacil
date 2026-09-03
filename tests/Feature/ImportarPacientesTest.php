<?php

namespace Tests\Feature;

use App\Filament\Doctor\Imports\PatientImporter;
use App\Filament\Doctor\Resources\PatientResource\Pages\ListPatients;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Traer los pacientes que el doctor ya tenía en su Excel.
 *
 * El archivo viene de una hoja de cálculo de verdad, no de un export limpio:
 * nombre completo en una sola columna, teléfonos con paréntesis y lada,
 * fechas en d/m/aaaa y "M"/"F" en el género.
 */
class ImportarPacientesTest extends TestCase
{
    use RefreshDatabase;

    private Clinic $clinica;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->clinica = Clinic::create([
            'name' => 'Consultorio Test',
            'slug' => 'consultorio-test',
            'plan' => 'profesional',
            'plan_ends_at' => now()->addMonth(),
            'onboarding_status' => 'completed',
        ]);

        $this->usuario = User::forceCreate([
            'name' => 'Dr. Roberto García',
            'email' => 'doctor@test.com',
            'password' => bcrypt('password'),
            'role' => 'doctor',
            'email_verified_at' => now(),
            'clinic_id' => $this->clinica->id,
        ]);
    }

    /**
     * Sube un CSV y devuelve el resultado de la importación.
     *
     * @param  array<string, string>  $mapa  columna del sistema => encabezado del archivo
     */
    private function importar(string $csv, array $mapa): void
    {
        Filament::setCurrentPanel(Filament::getPanel('doctor'));
        $this->actingAs($this->usuario);

        Livewire::test(ListPatients::class)
            ->callAction('import', data: [
                'file' => UploadedFile::fake()->createWithContent('pacientes.csv', $csv),
                'columnMap' => $mapa,
            ])
            ->assertHasNoActionErrors();
    }

    // ── Lo que llega de una hoja de cálculo de verdad ────────────

    public function test_importa_los_pacientes_del_archivo(): void
    {
        $csv = <<<CSV
        Nombre,Apellidos,Teléfono,Correo
        María Elena,García Ruiz,6682493398,maria@correo.com
        Juan,Pérez López,5512345678,juan@correo.com
        CSV;

        $this->importar($csv, [
            'first_name' => 'Nombre',
            'last_name' => 'Apellidos',
            'phone' => 'Teléfono',
            'email' => 'Correo',
        ]);

        $this->assertSame(2, Patient::withoutGlobalScopes()->count());

        $maria = Patient::withoutGlobalScopes()->where('phone', '6682493398')->firstOrFail();
        $this->assertSame('María Elena', $maria->first_name);
        $this->assertSame('García Ruiz', $maria->last_name);
        $this->assertSame('maria@correo.com', $maria->email);
    }

    public function test_los_pacientes_caen_en_el_consultorio_de_quien_subio_el_archivo(): void
    {
        // La importación corre en la cola, donde el scope de consultorio no
        // puede confiar en la sesión. Si esto falla, un paciente termina en
        // el consultorio de alguien más.
        $otra = Clinic::create([
            'name' => 'Otro Consultorio',
            'slug' => 'otro-consultorio',
            'plan' => 'basico',
            'onboarding_status' => 'completed',
        ]);

        $csv = "Nombre,Teléfono\nMaría,6682493398";

        $this->importar($csv, ['first_name' => 'Nombre', 'phone' => 'Teléfono']);

        $paciente = Patient::withoutGlobalScopes()->firstOrFail();

        $this->assertSame($this->clinica->id, $paciente->clinic_id);
        $this->assertSame(0, Patient::withoutGlobalScopes()->where('clinic_id', $otra->id)->count());
    }

    public function test_el_nombre_completo_en_una_sola_columna_se_parte(): void
    {
        // Lo normal en una hoja: una columna "Paciente" con todo junto.
        $csv = "Paciente,Teléfono\nJuan Carlos Pérez López,6682493398";

        $this->importar($csv, ['first_name' => 'Paciente', 'phone' => 'Teléfono']);

        $paciente = Patient::withoutGlobalScopes()->firstOrFail();

        $this->assertSame('Juan Carlos', $paciente->first_name);
        $this->assertSame('Pérez López', $paciente->last_name);
    }

    public function test_no_duplica_al_subir_el_mismo_archivo_dos_veces(): void
    {
        // Pasa seguido: el doctor corrige una columna y vuelve a subirlo.
        $csv = "Nombre,Teléfono,Correo\nMaría,(668) 249-3398,maria@correo.com";

        $this->importar($csv, ['first_name' => 'Nombre', 'phone' => 'Teléfono', 'email' => 'Correo']);
        $this->importar($csv, ['first_name' => 'Nombre', 'phone' => 'Teléfono', 'email' => 'Correo']);

        $this->assertSame(1, Patient::withoutGlobalScopes()->count());
    }

    public function test_reconoce_al_mismo_paciente_aunque_el_telefono_venga_escrito_distinto(): void
    {
        $this->importar("Nombre,Teléfono\nMaría,6682493398", ['first_name' => 'Nombre', 'phone' => 'Teléfono']);
        $this->importar("Nombre,Teléfono\nMaría,+52 668 249 3398", ['first_name' => 'Nombre', 'phone' => 'Teléfono']);

        $this->assertSame(1, Patient::withoutGlobalScopes()->count());
    }

    public function test_dos_pacientes_sin_telefono_ni_correo_no_se_confunden(): void
    {
        // Sin nada con qué reconocerlos, cada renglón es un paciente nuevo.
        $csv = "Nombre\nMaría García\nJuan Pérez";

        $this->importar($csv, ['first_name' => 'Nombre']);

        $this->assertSame(2, Patient::withoutGlobalScopes()->count());
    }

    // ── Teléfonos ────────────────────────────────────────────────

    /** @dataProvider telefonos */
    public function test_el_telefono_queda_en_diez_digitos(?string $entra, ?string $sale): void
    {
        $this->assertSame($sale, PatientImporter::telefonoLimpio($entra));
    }

    public static function telefonos(): array
    {
        return [
            'ya limpio' => ['6682493398', '6682493398'],
            'con paréntesis y guion' => ['(668) 249-3398', '6682493398'],
            'con lada de país' => ['+52 668 249 3398', '6682493398'],
            'con el 1 de Telcel' => ['+521 668 249 3398', '6682493398'],
            'con puntos' => ['668.249.3398', '6682493398'],
            'con espacios' => [' 668 249 3398 ', '6682493398'],
            'vacío' => ['', null],
            'nulo' => [null, null],
            'puro texto' => ['sin teléfono', null],
        ];
    }

    // ── Fechas ───────────────────────────────────────────────────

    /** @dataProvider fechas */
    public function test_la_fecha_se_lee_como_la_escribe_un_mexicano(?string $entra, ?string $sale): void
    {
        $this->assertSame($sale, PatientImporter::fechaMexicana($entra));
    }

    public static function fechas(): array
    {
        return [
            // 03/09 es 3 de septiembre, no 9 de marzo. Carbon::parse le pone
            // el mes primero y le cambia el cumpleaños a medio consultorio.
            'dia primero' => ['03/09/1985', '1985-09-03'],
            'con guiones' => ['15-03-1985', '1985-03-15'],
            'con puntos' => ['15.03.1985', '1985-03-15'],
            'año de dos dígitos' => ['15/03/85', '1985-03-15'],
            'formato ISO' => ['1985-03-15', '1985-03-15'],
            'en el futuro se descarta' => ['15/03/2090', null],
            'vacío' => ['', null],
            'basura' => ['no sé', null],
        ];
    }

    // ── Género y sangre ──────────────────────────────────────────

    /** @dataProvider generos */
    public function test_el_genero_se_normaliza(?string $entra, ?string $sale): void
    {
        $this->assertSame($sale, PatientImporter::generoNormalizado($entra));
    }

    public static function generos(): array
    {
        return [
            'M' => ['M', 'male'],
            'F' => ['F', 'female'],
            'Masculino' => ['Masculino', 'male'],
            'Femenino' => ['Femenino', 'female'],
            'Hombre' => ['Hombre', 'male'],
            'Mujer' => ['Mujer', 'female'],
            'con acento y mayúscula' => ['FEMENINO', 'female'],
            'vacío' => ['', null],
            'algo raro' => ['???', null],
        ];
    }

    /** @dataProvider sangres */
    public function test_el_tipo_de_sangre_se_normaliza(?string $entra, ?string $sale): void
    {
        $this->assertSame($sale, PatientImporter::sangreNormalizada($entra));
    }

    public static function sangres(): array
    {
        return [
            'ya bien' => ['O+', 'O+'],
            'minúscula' => ['o+', 'O+'],
            'con espacio' => ['O +', 'O+'],
            'escrito' => ['O positivo', 'O+'],
            'negativo escrito' => ['AB negativo', 'AB-'],
            'inválido' => ['Z+', null],
            'vacío' => ['', null],
        ];
    }

    // ── Partir el nombre ─────────────────────────────────────────

    /** @dataProvider nombres */
    public function test_el_nombre_completo_se_parte_como_se_acostumbra(string $completo, string $nombre, string $apellidos): void
    {
        $this->assertSame([$nombre, $apellidos], PatientImporter::partirNombre($completo));
    }

    public static function nombres(): array
    {
        return [
            'una palabra' => ['María', 'María', ''],
            'dos palabras' => ['María García', 'María', 'García'],
            'tres: nombre + dos apellidos' => ['María García Ruiz', 'María', 'García Ruiz'],
            'cuatro: dos nombres + dos apellidos' => ['Juan Carlos Pérez López', 'Juan Carlos', 'Pérez López'],
            'cinco' => ['María Elena del Carmen Pérez López', 'María Elena del Carmen', 'Pérez López'],
            'con espacios de más' => ['  Juan   Pérez  ', 'Juan', 'Pérez'],
        ];
    }

    // ── Correos ──────────────────────────────────────────────────

    public function test_el_correo_queda_en_minusculas(): void
    {
        $this->assertSame('maria@correo.com', PatientImporter::correoLimpio(' MARIA@Correo.com '));
    }

    public function test_un_correo_invalido_se_descarta_en_vez_de_tumbar_el_renglon(): void
    {
        // "no tiene" en la columna de correo es normal en estas hojas. Si se
        // dejara pasar, la validación tumbaría al paciente completo.
        $this->assertNull(PatientImporter::correoLimpio('no tiene'));
    }
}
