<?php

namespace App\Filament\Doctor\Widgets;

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

class CalendarWidget extends FullCalendarWidget
{
    protected function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Appointment::query()->where('clinic_id', auth()->user()->clinic_id);
    }

    // Only rendered inside CalendarPage, not on dashboard
    protected static bool $isDiscovered = false;

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
                // Soft pastel colors with darker border
                [$bg, $border, $text] = match ($appointment->status) {
                    'scheduled' => ['#fef3c7', '#f59e0b', '#92400e'],
                    'confirmed' => ['#dbeafe', '#3b82f6', '#1e3a8a'],
                    'in_progress' => ['#ede9fe', '#8b5cf6', '#4c1d95'],
                    'completed' => ['#d1fae5', '#10b981', '#064e3b'],
                    'cancelled' => ['#fde2e2', '#f87171', '#7f1d1d'],
                    'no_show' => ['#e5e7eb', '#9ca3af', '#374151'],
                    default => ['#ccfbf1', '#14b8a6', '#134e4a'],
                };

                $patientName = $appointment->patient
                    ? "{$appointment->patient->first_name} {$appointment->patient->last_name}"
                    : 'Sin paciente';

                $service = $appointment->service?->name ?? '';
                $doctor = $appointment->doctor?->user?->name ?? '';

                return EventData::make()
                    ->id($appointment->id)
                    ->title($patientName . ($service ? " · {$service}" : ''))
                    ->start($appointment->starts_at)
                    ->end($appointment->ends_at)
                    ->backgroundColor($bg)
                    ->borderColor($border)
                    ->textColor($text)
                    ->extendedProp('doctor', $doctor)
                    ->extendedProp('status', $appointment->status)
                    ->extendedProp('phone', $appointment->patient?->phone ?? '');
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
                // Rounded corners and better styling
                el.style.borderRadius = '8px';
                el.style.padding = '2px 6px';
                el.style.fontSize = '0.8rem';
                el.style.fontWeight = '500';
                el.style.borderLeftWidth = '3px';
                el.style.cursor = 'pointer';

                // Tooltip with details
                const doctor = event.extendedProps?.doctor || '';
                const status = event.extendedProps?.status || '';
                const statusLabel = {scheduled:'Programada',confirmed:'Confirmada',in_progress:'En consulta',completed:'Completada',cancelled:'Cancelada',no_show:'No asistió'}[status] || status;
                el.setAttribute('title', `${event.title}\n👨‍⚕️ ${doctor}\n📋 ${statusLabel}`);
            }
        JS;
    }

    public function onEventClick(array $event): void
    {
        $this->redirect(route('filament.doctor.pages.consultation', ['appointment' => $event['id']]));
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
