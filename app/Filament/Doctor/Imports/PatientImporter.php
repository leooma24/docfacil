<?php

namespace App\Filament\Doctor\Imports;

use App\Models\Patient;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Traer los pacientes que el doctor ya tenía en su Excel.
 *
 * Hasta ahora no había ninguna forma de importar: un dentista con 300
 * pacientes tendría que capturarlos a mano uno por uno. Esa es la razón más
 * común de que alguien diga "sí, se ve bien" y nunca empiece.
 *
 * El archivo viene de una hoja de cálculo de verdad, no de un export limpio,
 * así que aquí se asume lo peor: nombre completo en una sola columna,
 * teléfonos con paréntesis y lada, fechas en d/m/aaaa, "M" y "F" en el
 * género, y renglones repetidos.
 */
class PatientImporter extends Importer
{
    protected static ?string $model = Patient::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('first_name')
                ->label('Nombre')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                // Los encabezados que de verdad se ven en las hojas de los
                // consultorios. Filament los usa para adivinar el mapeo.
                ->guess(['nombre', 'nombres', 'nombre(s)', 'paciente', 'nombre del paciente', 'nombre completo', 'name', 'first name'])
                ->example('María Elena'),

            ImportColumn::make('last_name')
                ->label('Apellidos')
                ->rules(['nullable', 'string', 'max:255'])
                ->guess(['apellido', 'apellidos', 'apellido paterno', 'apellidos del paciente', 'last name', 'surname'])
                ->example('García Ruiz'),

            ImportColumn::make('phone')
                ->label('Teléfono')
                ->rules(['nullable', 'string', 'max:20'])
                ->guess(['telefono', 'teléfono', 'tel', 'celular', 'cel', 'whatsapp', 'wa', 'movil', 'móvil', 'phone', 'numero', 'número'])
                ->castStateUsing(fn (?string $state) => self::telefonoLimpio($state))
                ->example('6682493398'),

            ImportColumn::make('email')
                ->label('Correo')
                ->rules(['nullable', 'email', 'max:255'])
                ->guess(['correo', 'email', 'e-mail', 'correo electronico', 'correo electrónico', 'mail'])
                ->castStateUsing(fn (?string $state) => self::correoLimpio($state))
                ->example('maria@correo.com'),

            ImportColumn::make('birth_date')
                ->label('Fecha de nacimiento')
                ->rules(['nullable', 'date'])
                ->guess(['fecha de nacimiento', 'nacimiento', 'fecha nacimiento', 'cumpleanos', 'cumpleaños', 'f. nac', 'f nac', 'fecha nac', 'birth date', 'birthday', 'edad'])
                ->castStateUsing(fn (?string $state) => self::fechaMexicana($state))
                ->example('15/03/1985'),

            ImportColumn::make('gender')
                ->label('Género')
                ->rules(['nullable', 'in:male,female,other'])
                ->guess(['genero', 'género', 'sexo', 'gender', 'sex'])
                ->castStateUsing(fn (?string $state) => self::generoNormalizado($state))
                ->example('Femenino'),

            ImportColumn::make('address')
                ->label('Dirección')
                ->rules(['nullable', 'string', 'max:500'])
                ->guess(['direccion', 'dirección', 'domicilio', 'calle', 'address'])
                ->example('Av. Álvaro Obregón 123'),

            ImportColumn::make('allergies')
                ->label('Alergias')
                ->rules(['nullable', 'string', 'max:1000'])
                ->guess(['alergias', 'alergia', 'allergies'])
                ->example('Penicilina'),

            ImportColumn::make('blood_type')
                ->label('Tipo de sangre')
                ->rules(['nullable', 'string', 'max:5'])
                ->guess(['tipo de sangre', 'sangre', 'grupo sanguineo', 'grupo sanguíneo', 'blood type'])
                ->castStateUsing(fn (?string $state) => self::sangreNormalizada($state))
                ->example('O+'),

            ImportColumn::make('medical_notes')
                ->label('Notas')
                ->rules(['nullable', 'string', 'max:2000'])
                ->guess(['notas', 'observaciones', 'comentarios', 'notas medicas', 'notas médicas', 'antecedentes', 'notes'])
                ->example('Hipertensión controlada'),
        ];
    }

    /**
     * El paciente que ya existe, o uno nuevo.
     *
     * Sin esto, importar dos veces el mismo archivo — que pasa seguido,
     * porque el doctor corrige una columna y vuelve a subirlo — dejaba la
     * base con todo duplicado. Se busca por teléfono y luego por correo,
     * que es lo único que de verdad identifica a alguien en estas hojas.
     */
    public function resolveRecord(): Patient
    {
        $clinicId = $this->clinicId();

        // Ya vienen normalizados: castData() corre antes que esto, asi que
        // el telefono es de puros digitos y el correo en minusculas. Por eso
        // se puede comparar directo contra lo que hay guardado.
        $telefono = $this->data['phone'] ?? null;
        $correo = $this->data['email'] ?? null;

        $existente = Patient::withoutGlobalScopes()
            ->where('clinic_id', $clinicId)
            ->where(function ($query) use ($telefono, $correo) {
                if ($telefono) {
                    $query->orWhere('phone', $telefono);
                }
                if ($correo) {
                    $query->orWhere('email', $correo);
                }
                // Sin teléfono ni correo no hay con qué reconocerlo: se crea.
                if (! $telefono && ! $correo) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->first();

        if ($existente) {
            return $existente;
        }

        $nuevo = new Patient();
        $nuevo->clinic_id = $clinicId;

        return $nuevo;
    }

    /**
     * Antes de guardar: partir el nombre completo y amarrar el consultorio.
     */
    protected function beforeSave(): void
    {
        // "Juan Pérez López" en una sola columna es lo normal en una hoja de
        // cálculo. Si el doctor solo mapeó Nombre, se parte aquí en vez de
        // dejarle 300 pacientes que se llaman "Juan Pérez López" de nombre.
        if (blank($this->record->last_name) && filled($this->record->first_name)) {
            [$nombre, $apellidos] = self::partirNombre($this->record->first_name);

            $this->record->first_name = $nombre;
            $this->record->last_name = $apellidos;
        }

        // El clinic_id nunca sale del archivo. La importación corre en la
        // cola, donde el scope de consultorio no puede confiar en la sesión,
        // así que se pone explícito: sin esto un paciente podría caer en
        // otro consultorio.
        $this->record->clinic_id = $this->clinicId();

        if (blank($this->record->is_active)) {
            $this->record->is_active = true;
        }
    }

    /** El consultorio de quien subió el archivo. */
    protected function clinicId(): int
    {
        return (int) $this->import->user->clinic_id;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $importadas = number_format($import->successful_rows);
        $cuerpo = "Se importaron {$importadas} " . Str::plural('paciente', $import->successful_rows) . '.';

        if ($fallidas = $import->getFailedRowsCount()) {
            $cuerpo .= ' ' . number_format($fallidas) . ' '
                . Str::plural('renglón', $fallidas) . ' no se '
                . ($fallidas === 1 ? 'pudo' : 'pudieron')
                . ' importar — descarga el archivo de errores para ver cuáles y por qué.';
        }

        return $cuerpo;
    }

    // ── Limpieza de lo que viene en la hoja ──────────────────────

    /**
     * El teléfono, a puros 10 dígitos.
     *
     * En las hojas viene de todas las formas: "(668) 249-3398",
     * "+52 668 249 3398", "668.249.3398". Si no se normaliza, el
     * WhatsApp no marca y los duplicados no se reconocen.
     */
    public static function telefonoLimpio(?string $valor): ?string
    {
        if (blank($valor)) {
            return null;
        }

        $digitos = preg_replace('/\D+/', '', $valor);

        if (blank($digitos)) {
            return null;
        }

        // Lada de país: 52 al frente cuando lo que queda son 10 dígitos.
        if (strlen($digitos) === 12 && str_starts_with($digitos, '52')) {
            $digitos = substr($digitos, 2);
        }

        // El 1 que Telcel metía después del 52 y que muchos siguen anotando.
        if (strlen($digitos) === 13 && str_starts_with($digitos, '521')) {
            $digitos = substr($digitos, 3);
        }

        return $digitos;
    }

    public static function correoLimpio(?string $valor): ?string
    {
        if (blank($valor)) {
            return null;
        }

        $correo = mb_strtolower(trim($valor));

        return filter_var($correo, FILTER_VALIDATE_EMAIL) ? $correo : null;
    }

    /**
     * La fecha, leída como la escribe un mexicano.
     *
     * 03/09/2026 es 3 de septiembre, no 9 de marzo. Carbon::parse le pone
     * mes primero y le cambia el cumpleaños a medio consultorio.
     */
    public static function fechaMexicana(?string $valor): ?string
    {
        if (blank($valor)) {
            return null;
        }

        $valor = trim($valor);

        // aaaa-mm-dd: ya viene sin ambigüedad.
        if (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $valor)) {
            return self::fechaValida($valor);
        }

        // d/m/aaaa, d-m-aaaa, d.m.aaaa — el formato de acá.
        if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})$/', $valor, $partes)) {
            [, $dia, $mes, $anio] = $partes;

            if (strlen($anio) === 2) {
                // "85" es 1985, no 2085: nadie nace en el futuro.
                $anio = (int) $anio > (int) date('y') ? '19' . $anio : '20' . $anio;
            }

            return self::fechaValida(sprintf('%04d-%02d-%02d', $anio, $mes, $dia));
        }

        // "15 de marzo de 1985" y demás: que lo intente Carbon.
        try {
            return Carbon::parse($valor)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function fechaValida(string $fecha): ?string
    {
        try {
            $carbon = Carbon::createFromFormat('Y-m-d', $fecha);

            // Una fecha de nacimiento en el futuro es un dedazo, no un dato.
            return $carbon && ! $carbon->isFuture() ? $carbon->toDateString() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** "M", "F", "Masculino", "Mujer", "Hombre"... todo a male/female/other. */
    public static function generoNormalizado(?string $valor): ?string
    {
        if (blank($valor)) {
            return null;
        }

        $v = Str::of($valor)->lower()->ascii()->trim()->toString();

        return match (true) {
            in_array($v, ['m', 'h', 'masculino', 'hombre', 'male', 'varon'], true) => 'male',
            in_array($v, ['f', 'femenino', 'mujer', 'female'], true) => 'female',
            in_array($v, ['o', 'otro', 'other'], true) => 'other',
            default => null,
        };
    }

    /** "o positivo", "O +", "o+" → "O+". */
    public static function sangreNormalizada(?string $valor): ?string
    {
        if (blank($valor)) {
            return null;
        }

        $v = Str::of($valor)->lower()->ascii()->replace(' ', '')->trim()->toString();
        $v = str_replace(['positivo', 'negativo', 'pos', 'neg'], ['+', '-', '+', '-'], $v);
        $v = strtoupper($v);

        return in_array($v, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], true) ? $v : null;
    }

    /**
     * Partir "Juan Carlos Pérez López" en nombre y apellidos.
     *
     * Acá se acostumbra nombre(s) + paterno + materno. Con cuatro palabras
     * o más se parte a la mitad; con tres, la primera es el nombre; con dos,
     * una y una. No es exacto — es mejor que dejar todo en un campo, y el
     * doctor lo corrige de a uno si algo salió chueco.
     */
    public static function partirNombre(string $completo): array
    {
        $palabras = preg_split('/\s+/', trim($completo), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $cuantas = count($palabras);

        if ($cuantas <= 1) {
            return [$completo, ''];
        }

        $corte = match (true) {
            $cuantas === 2 => 1,
            $cuantas === 3 => 1,
            default => $cuantas - 2,
        };

        return [
            implode(' ', array_slice($palabras, 0, $corte)),
            implode(' ', array_slice($palabras, $corte)),
        ];
    }
}
