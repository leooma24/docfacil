<?php

namespace App\Filament\Doctor\Pages;

use App\Filament\Doctor\Imports\AppointmentImporter;
use App\Filament\Doctor\Imports\PatientImporter;
use App\Filament\Doctor\Imports\SaldoImporter;
use Filament\Actions\ImportAction;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

/**
 * Cambiarse a DocFácil sin perder nada (día 3 del plan, 10-oct-2026): sus
 * pacientes, sus citas que vienen y lo que le deben, desde Excel, Google
 * Sheets o lo que exporte su sistema anterior. En ese orden, porque las citas
 * y los saldos reconocen a los pacientes que ya subió.
 */
class TraerMisDatos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Traer sus datos';

    protected static ?string $title = 'Traiga sus datos';

    protected static ?string $slug = 'traer-mis-datos';

    protected static string $view = 'filament.doctor.pages.traer-mis-datos';

    protected static ?string $navigationGroup = 'Mi cuenta';

    private static function comoSubirlo(string $que): HtmlString
    {
        return new HtmlString(
            '<p style="margin-bottom:.5rem;">' . e($que) . '</p>'
            . '<p style="margin-bottom:.5rem;"><strong>Si está en Excel:</strong> Archivo → Guardar como → CSV UTF-8.</p>'
            . '<p style="margin-bottom:.5rem;"><strong>Si está en Google Sheets:</strong> Archivo → Descargar → Valores separados por comas (.csv).</p>'
            . '<p>Después le dejamos decir qué columna es cuál. No importa el orden ni cómo se llamen. Subirlo dos veces no duplica nada.</p>'
        );
    }

    public function importarPacientesAction(): ImportAction
    {
        return ImportAction::make('importarPacientes')
            ->importer(PatientImporter::class)
            ->label('Subir pacientes')
            ->icon('heroicon-o-user-group')
            ->modalHeading('Sus pacientes')
            ->modalDescription(self::comoSubirlo('Nombre y teléfono bastan. Si trae la CURP, llena sola la fecha de nacimiento y el sexo.'))
            ->modalSubmitActionLabel('Subir');
    }

    public function importarCitasAction(): ImportAction
    {
        return ImportAction::make('importarCitas')
            ->importer(AppointmentImporter::class)
            ->label('Subir citas')
            ->icon('heroicon-o-calendar-days')
            ->modalHeading('Sus citas que vienen')
            ->modalDescription(self::comoSubirlo('Paciente, fecha y hora. Si el paciente no está, lo damos de alta. Las que ya pasaron no se suben.'))
            ->modalSubmitActionLabel('Subir');
    }

    public function importarSaldosAction(): ImportAction
    {
        return ImportAction::make('importarSaldos')
            ->importer(SaldoImporter::class)
            ->label('Subir saldos')
            ->icon('heroicon-o-banknotes')
            // Es dinero: la asistente lo ve solo si tiene permiso del dinero.
            ->visible(fn () => auth()->user()->veElDinero())
            ->modalHeading('Lo que le deben')
            ->modalDescription(self::comoSubirlo('Paciente y cuánto debe. Cada renglón queda como un cobro pendiente y sale en «Le deben».'))
            ->modalSubmitActionLabel('Subir');
    }

    public function getViewData(): array
    {
        $clinicId = auth()->user()->clinic_id;

        return [
            'pacientes' => \App\Models\Patient::where('clinic_id', $clinicId)->count(),
            'citas' => \App\Models\Appointment::where('clinic_id', $clinicId)->where('starts_at', '>=', now())->count(),
            'porCobrar' => \App\Models\Payment::saldoPorCobrar($clinicId),
        ];
    }
}
