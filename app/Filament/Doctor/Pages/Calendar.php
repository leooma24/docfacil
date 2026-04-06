<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class Calendar extends FullCalendarWidget
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Calendario';

    protected static ?int $navigationSort = -1;

    public Model|string|null $model = Appointment::class;

    public function config(): array
    {
        return [
            'locale' => 'es',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            ],
            'initialView' => 'timeGridWeek',
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '21:00:00',
            'allDaySlot' => false,
            'nowIndicator' => true,
            'editable' => true,
            'selectable' => true,
            'slotDuration' => '00:15:00',
            'height' => 'auto',
            'buttonText' => [
                'today' => 'Hoy',
                'month' => 'Mes',
                'week' => 'Semana',
                'day' => 'Día',
                'list' => 'Lista',
            ],
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $clinicId = auth()->user()->clinic_id;

        return Appointment::query()
            ->where('clinic_id', $clinicId)
            ->where('starts_at', '>=', $fetchInfo['start'])
            ->where('ends_at', '<=', $fetchInfo['end'])
            ->with(['patient', 'doctor.user', 'service'])
            ->get()
            ->map(function (Appointment $appointment) {
                $color = match ($appointment->status) {
                    'scheduled' => '#f59e0b',
                    'confirmed' => '#3b82f6',
                    'in_progress' => '#8b5cf6',
                    'completed' => '#10b981',
                    'cancelled' => '#ef4444',
                    'no_show' => '#6b7280',
                    default => '#14b8a6',
                };

                $patientName = $appointment->patient
                    ? "{$appointment->patient->first_name} {$appointment->patient->last_name}"
                    : 'Sin paciente';

                return EventData::make()
                    ->id($appointment->id)
                    ->title($patientName . ($appointment->service ? " - {$appointment->service->name}" : ''))
                    ->start($appointment->starts_at)
                    ->end($appointment->ends_at)
                    ->backgroundColor($color)
                    ->borderColor($color);
            })
            ->toArray();
    }

    public function getFormSchema(): array
    {
        $clinicId = auth()->user()->clinic_id;

        return [
            Forms\Components\Select::make('patient_id')
                ->label('Paciente')
                ->options(
                    Patient::where('clinic_id', $clinicId)
                        ->get()
                        ->mapWithKeys(fn ($p) => [$p->id => "{$p->first_name} {$p->last_name}"])
                )
                ->searchable()
                ->required(),
            Forms\Components\Select::make('doctor_id')
                ->label('Doctor')
                ->options(
                    Doctor::where('clinic_id', $clinicId)
                        ->with('user')
                        ->get()
                        ->mapWithKeys(fn ($d) => [$d->id => $d->user?->name ?? ''])
                )
                ->required(),
            Forms\Components\Select::make('service_id')
                ->label('Servicio')
                ->options(
                    Service::where('clinic_id', $clinicId)
                        ->where('is_active', true)
                        ->pluck('name', 'id')
                ),
            Forms\Components\DateTimePicker::make('starts_at')
                ->label('Inicio')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y H:i')
                ->minutesStep(15),
            Forms\Components\DateTimePicker::make('ends_at')
                ->label('Fin')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y H:i')
                ->minutesStep(15),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'scheduled' => 'Programada',
                    'confirmed' => 'Confirmada',
                    'completed' => 'Completada',
                    'cancelled' => 'Cancelada',
                ])
                ->default('scheduled'),
            Forms\Components\Textarea::make('notes')
                ->label('Notas')
                ->rows(2),
        ];
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nueva cita')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['clinic_id'] = auth()->user()->clinic_id;
                    return $data;
                }),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar'),
            Actions\DeleteAction::make()
                ->label('Eliminar'),
        ];
    }

    public function eventDidMount(): string
    {
        return <<<'JS'
            function({ event, el }) {
                el.setAttribute('title', event.title);
            }
        JS;
    }

    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        $appointment = Appointment::where('clinic_id', auth()->user()->clinic_id)->find($event['id']);
        if ($appointment) {
            $appointment->update([
                'starts_at' => $event['start'],
                'ends_at' => $event['end'] ?? \Carbon\Carbon::parse($event['start'])->addMinutes(30),
            ]);
        }
        return true;
    }

    public function onEventResize(array $event, array $oldEvent, array $relatedEvents, array $startDelta, array $endDelta): bool
    {
        return $this->onEventDrop($event, $oldEvent, $relatedEvents, $endDelta, null, null);
    }
}
