<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Doctor\Concerns\SearchesPatientName;

class AppointmentResource extends Resource
{
    use SearchesPatientName;

    protected static ?string $slug = 'citas';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Citas';

    protected static ?string $modelLabel = 'Cita';

    protected static ?string $pluralModelLabel = 'Citas';

    protected static ?int $navigationSort = 3;

    /**
     * Cuántas citas quedan hoy: lo primero que el doctor quiere saber. Las de
     * horas que ya pasaron no cuentan (con 15 minutos de tolerancia), igual
     * que en el botón principal del menú.
     */
    public static function getNavigationBadge(): ?string
    {
        $clinicId = auth()->user()?->clinic_id;
        if (! $clinicId) {
            return null;
        }

        $n = Appointment::where('clinic_id', $clinicId)
            ->whereBetween('starts_at', [now()->subMinutes(15), today()->endOfDay()])
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->count();

        return $n ? (string) $n : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Citas que faltan hoy';
    }

    /**
     * Lo que se cobra en una cita: lo que quedó pendiente de ella o, si es un
     * control de ortodoncia, las mensualidades de su plan que ya tocan (día 7
     * del plan, 10-oct-2026): el control del mes y su mensualidad son un solo
     * cobro.
     */
    public static function porCobrarDeLaCita(Appointment $cita): \Illuminate\Support\Collection
    {
        $pendientes = $cita->payments()->whereIn('status', ['pending', 'partial'])->get();

        return $pendientes->isNotEmpty() || $cita->payments()->exists()
            ? $pendientes
            : \App\Support\TableroDeOrtodoncia::mensualidadesPorCobrar($cita);
    }

    /** Cuánto dura una cita de ese servicio (30 minutos si no hay servicio). */
    public static function duracionDe(mixed $servicioId): int
    {
        $minutos = $servicioId ? (int) Service::where('clinic_id', auth()->user()->clinic_id)->whereKey($servicioId)->value('duration_minutes') : 0;

        return $minutos > 0 ? $minutos : 30;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('La cita')
                    ->description('Paciente, doctor y hora. La lupa busca el primer hueco libre.')
                    ->icon('heroicon-o-calendar-days')
                    ->aside()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient')
                            ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required()
                            ->live()
                            // Antes de apartarle el horario, decirle al doctor
                            // si este paciente ya movio sus citas varias veces:
                            // el que reagenda tres veces casi nunca llega a la
                            // cuarta, y eso conviene saberlo antes, no despues.
                            ->helperText(function (Forms\Get $get) {
                                $paciente = $get('patient_id')
                                    ? Patient::find($get('patient_id'))
                                    : null;

                                if (! $paciente || ! $paciente->reagendaDeMas()) {
                                    return null;
                                }

                                $veces = $paciente->vecesQueHaMovidoCitas();

                                return new \Illuminate\Support\HtmlString(
                                    '<span style="color:#b45309;font-weight:600;">⚠️ Ha movido sus citas '
                                    . $veces . ' veces en los últimos 6 meses. Confírmele por WhatsApp antes de apartarle el horario.</span>'
                                );
                            })
                            // Pre-fill desde ?patient= cuando se llega desde
                            // el profile del paciente. Closure para que se
                            // evalue por request, no en compile.
                            ->default(fn () => request()->query('patient'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('first_name')->label('Nombre')->required(),
                                Forms\Components\TextInput::make('last_name')->label('Apellidos')->required(),
                                Forms\Components\TextInput::make('phone')->label('Teléfono')->tel(),
                                Forms\Components\TextInput::make('email')->label('Email')->email(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['clinic_id'] = auth()->user()->clinic_id;
                                return Patient::create($data)->id;
                            }),
                        Forms\Components\Select::make('doctor_id')
                            ->label('Doctor')
                            ->relationship('doctor')
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record) => $record->user?->name ?? '')
                            ->preload()
                            ->required()
                            // Doctor logueado por default. El usuario puede
                            // cambiarlo en el dropdown sin que se revierta
                            // (porque es declared default, no mount override).
                            // Desde «El día por doctor», el doctor del hueco que se tocó.
                            ->default(fn () => \App\Models\Doctor::where('clinic_id', auth()->user()?->clinic_id)->whereKey(request()->query('doctor'))->value('id')
                                ?? auth()->user()?->doctor?->id),
                        Forms\Components\Select::make('service_id')
                            ->label('Servicio')
                            ->relationship('service', 'name')
                            // Desde el tablero de ortodoncia: «Agendar control».
                            ->default(fn () => Service::where('clinic_id', auth()->user()?->clinic_id)->whereKey(request()->query('servicio'))->value('id'))
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if (!$state) return;
                                $service = Service::find($state);
                                if (!$service) return;

                                $set('duration_hint', "{$service->duration_minutes} min - \${$service->price}");

                                // Auto-calcular ends_at si ya hay starts_at.
                                // Si no hay starts_at todavia, no hacemos nada — se calculara
                                // cuando se llene starts_at (logica simétrica abajo).
                                $start = $get('starts_at');
                                if ($start) {
                                    $end = \Carbon\Carbon::parse($start)->addMinutes((int) $service->duration_minutes);
                                    $set('ends_at', $end->format('Y-m-d H:i:s'));
                                }
                            }),
                        Forms\Components\Placeholder::make('duration_hint')
                            ->label('Duración / Precio')
                            ->content(function (Forms\Get $get) {
                                $service = $get('service_id') ? Service::find($get('service_id')) : null;
                                return $service ? "{$service->duration_minutes} min - \${$service->price}" : '-';
                            }),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Inicio')
                            ->default(fn () => preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) request()->query('inicio'))
                                ? \Carbon\Carbon::parse(request()->query('inicio'))->format('Y-m-d H:i:s') : null)
                            ->helperText(fn (string $operation) => $operation === 'create' ? 'La lupa busca el primer hueco libre de este doctor.' : null)
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15)
                            // «Buscar espacio» (día 6 del plan, 10-oct-2026): el
                            // primer hueco libre de ese doctor que alcance para el
                            // servicio, contando horario, comida, cierres y sus citas.
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('buscarEspacio')
                                    ->label('Buscar espacio')
                                    ->tooltip('Buscar el primer hueco libre')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->action(function (Forms\Get $get, Forms\Set $set) {
                                        $minutos = self::duracionDe($get('service_id'));
                                        $agenda = \App\Services\HuecosDisponibles::proximosDias(auth()->user()->clinic, $get('doctor_id') ? (int) $get('doctor_id') : null, $minutos, 1);
                                        $fecha = array_key_first($agenda);
                                        if (! $fecha) {
                                            Notification::make()->title('No hay huecos libres en los próximos 60 días')->warning()->send();

                                            return;
                                        }
                                        $inicio = \Carbon\Carbon::parse($fecha . ' ' . $agenda[$fecha][0]);
                                        $set('starts_at', $inicio->format('Y-m-d H:i:s'));
                                        $set('ends_at', $inicio->copy()->addMinutes($minutos)->format('Y-m-d H:i:s'));
                                    })
                            )
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                // Cuando cambian el inicio, recalcular el fin segun la duracion
                                // del servicio (si hay servicio seleccionado). Si no hay servicio
                                // poner +30 min como default razonable.
                                if (!$state) return;
                                $serviceId = $get('service_id');
                                $minutes = 30; // default si no hay servicio
                                if ($serviceId) {
                                    $service = Service::find($serviceId);
                                    if ($service) {
                                        $minutes = (int) $service->duration_minutes;
                                    }
                                }
                                $end = \Carbon\Carbon::parse($state)->addMinutes($minutes);
                                $set('ends_at', $end->format('Y-m-d H:i:s'));
                            })
                            // No dejar encimar dos citas del mismo doctor. Antes
                            // se guardaban las dos sin decir nada, y el traslape
                            // se descubria cuando llegaban los dos pacientes.
                            ->rule(function (Forms\Get $get, ?Appointment $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $fin = $get('ends_at');

                                    if (! $value || ! $fin) {
                                        return;
                                    }

                                    $choque = Appointment::mensajeDeTraslape(
                                        auth()->user()->clinic_id,
                                        $get('doctor_id') ? (int) $get('doctor_id') : null,
                                        \Carbon\Carbon::parse($value),
                                        \Carbon\Carbon::parse($fin),
                                        $record?->id,
                                    );

                                    if ($choque) {
                                        $fail($choque);
                                    }
                                };
                            }),
                        // Los huecos libres de ese día para ese doctor: tocar uno
                        // pone la hora. Así no se escribe una hora a ciegas.
                        Forms\Components\ToggleButtons::make('hueco')
                            ->label(fn (Forms\Get $get) => 'Libres el ' . \Carbon\Carbon::parse($get('starts_at') ?: today())->locale('es')->isoFormat('dddd D [de] MMMM'))
                            ->options(fn (Forms\Get $get) => collect(\App\Services\HuecosDisponibles::delDia(
                                auth()->user()->clinic,
                                \Carbon\CarbonImmutable::parse($get('starts_at') ?: today())->startOfDay(),
                                $get('doctor_id') ? (int) $get('doctor_id') : null,
                                self::duracionDe($get('service_id')),
                            ))->mapWithKeys(fn ($h) => [$h => $h])->all())
                            ->inline()
                            ->live()
                            ->dehydrated(false)
                            ->helperText(fn (Forms\Components\ToggleButtons $component) => $component->getOptions() === []
                                ? 'Ese día no hay huecos libres para este doctor. Toque la lupa de Inicio para buscar el siguiente.' : null)
                            ->visible(fn (string $operation, Forms\Get $get) => $operation === 'create' && filled($get('starts_at')))
                            ->afterStateUpdated(function (?string $state, Forms\Get $get, Forms\Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $inicio = \Carbon\Carbon::parse(\Carbon\Carbon::parse($get('starts_at') ?: today())->toDateString() . ' ' . $state);
                                $set('starts_at', $inicio->format('Y-m-d H:i:s'));
                                $set('ends_at', $inicio->copy()->addMinutes(self::duracionDe($get('service_id')))->format('Y-m-d H:i:s'));
                            })
                            ->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fin')
                            ->default(fn () => preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) request()->query('inicio'))
                                ? \Carbon\Carbon::parse(request()->query('inicio'))->addMinutes(30)->format('Y-m-d H:i:s') : null)
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15)
                            ->helperText('Se calcula automáticamente al elegir servicio + inicio. Puede ajustarlo.'),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'scheduled' => 'Programada',
                                'confirmed' => 'Confirmada',
                                'in_progress' => 'En curso',
                                'completed' => 'Completada',
                                'cancelled' => 'Cancelada',
                                'no_show' => 'No asistió',
                            ])
                            ->default('scheduled')
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Fecha/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    // El nombre lleva al perfil del paciente: de cualquier lista se llega a todo lo suyo.
                    ->url(fn ($record) => \App\Filament\Doctor\Pages\PatientProfile::getUrl(['patient' => $record->patient_id], panel: 'doctor'))
                    ->color('primary')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->searchable(query: self::buscarPorNombreDePaciente()),
                // Lo importante del paciente, a la vista desde la lista: no hay
                // que abrir su consulta para enterarse de la alergia.
                Tables\Columns\TextColumn::make('alertas')
                    ->label('Alertas')
                    ->state(fn ($record) => [...(\App\Models\LabOrder::pendienteParaCita($record->id) ? ['Lab: no ha llegado'] : []), ...\App\Support\AlertasClinicas::etiquetas($record->patient),
                        // Lo que falta para atenderlo y recordarle (día 10 del plan).
                        ...(($falta = $record->patient?->datosQueFaltan() ?? []) ? ['Faltan datos: ' . implode(', ', $falta)] : [])])
                    ->badge()
                    ->color(fn (string $state) => str_starts_with($state, 'Faltan datos') ? 'warning' : 'danger')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->visibleFrom('2xl')
                    ->label('Doctor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->visibleFrom('md')
                    ->label('Servicio')
                    ->placeholder('Sin servicio'),
                // Solo aparece cuando la cita ya se movio: el que reagenda
                // tres veces casi nunca llega a la cuarta.
                Tables\Columns\TextColumn::make('veces_reagendada')
                    ->visibleFrom('2xl')
                    ->label('Movida')
                    ->badge()
                    ->sortable()
                    ->color(fn (?int $state) => $state >= Appointment::REAGENDADAS_PARA_PREOCUPARSE ? 'danger' : 'warning')
                    ->tooltip('Veces que esta cita ha cambiado de horario')
                    ->formatStateUsing(fn (?int $state) => $state === 1 ? '1 vez' : "{$state} veces")
                    ->placeholder('')
                    ->getStateUsing(fn (Appointment $record) => (int) $record->veces_reagendada ?: null),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'scheduled',
                        'info' => 'confirmed',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => fn ($state) => in_array($state, ['cancelled', 'no_show']),
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'scheduled' => 'Programada',
                        'confirmed' => 'Confirmada',
                        'in_progress' => 'En curso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'No asistió',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'scheduled' => 'Programada',
                        'confirmed' => 'Confirmada',
                        'in_progress' => 'En curso',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                        'no_show' => 'No asistió',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Hoy')
                    ->query(fn ($query) => $query->whereDate('starts_at', today())),
                // A quién falta recordarle la cita de mañana. Las confirmadas
                // no: el paciente ya dijo que viene.
                Tables\Filters\Filter::make('sin_recordatorio')
                    ->label('Mañana, sin recordatorio')
                    ->query(fn ($query) => $query->whereDate('starts_at', today()->addDay())
                        ->where('status', 'scheduled')
                        ->where('reminder_sent', false)),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Próximas')
                    ->query(fn ($query) => $query->where('starts_at', '>=', now()))
                    ->default(),
            ])
            ->actions([
                // Fuera del menú, a la vista: recordarle a cada paciente de
                // mañana es un clic, no dos (Acciones → WhatsApp).
                Tables\Actions\Action::make('whatsapp')
                    // Pasa por DocFácil para dejarla recordada (y a las demás
                    // citas del paciente ese día) y abre WhatsApp con un solo
                    // mensaje. Ver la ruta cita.recordar.
                    ->label(fn (Appointment $record) => $record->reminder_sent ? 'Recordado' : 'WhatsApp')
                    ->icon(fn (Appointment $record) => $record->reminder_sent ? 'heroicon-o-check-circle' : 'heroicon-o-chat-bubble-left-ellipsis')
                    ->color(fn (Appointment $record) => $record->reminder_sent ? 'gray' : 'success')
                    ->tooltip(fn (Appointment $record) => $record->reminder_sent ? 'Ya se le mandó el recordatorio. Tóquelo para mandarlo otra vez.' : 'Mandar recordatorio por WhatsApp')
                    ->visible(fn (Appointment $record) => !empty($record->patient->telefonoDeContacto()) && in_array($record->status, ['scheduled', 'confirmed']))
                    ->url(fn (Appointment $record) => route('cita.recordar', $record))
                    ->openUrlInNewTab()
                    // Solo el ícono: con la palabra, "Acciones" se salía de la
                    // tabla en una laptop de 1366.
                    ->iconButton()
                    ->size('lg'),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('start_consultation')
                        ->label('Iniciar consulta')
                        ->icon('heroicon-o-play-circle')
                        ->color('primary')
                        ->url(fn (Appointment $record) => route('filament.doctor.pages.consulta', ['appointment' => $record->id]))
                        ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed', 'in_progress'])),

                    // Visita rápida: para ortodoncia / controles / chequeos repetitivos.
                    // 3 clicks: nota opcional + próxima cita auto-sugerida + (opcional) cobro.
                    Tables\Actions\Action::make('quick_visit')
                        ->label('Visita rápida')
                        ->icon('heroicon-o-bolt')
                        ->color('info')
                        ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed', 'in_progress']))
                        ->fillForm(function (Appointment $record): array {
                            $suggested = \App\Services\AppointmentPatternService::suggestNextDate(
                                $record->patient_id,
                                $record->clinic_id
                            );
                            return [
                                'note' => '',
                                'next_appointment_date' => $suggested?->format('Y-m-d\TH:i'),
                                'next_service_id' => $record->service_id,
                                'charge' => false,
                                'amount' => $record->service?->price,
                                'payment_method' => 'cash',
                            ];
                        })
                        ->form(fn (Appointment $record) => [
                            Forms\Components\Placeholder::make('paciente_info')
                                ->label('')
                                ->content(fn () => new \Illuminate\Support\HtmlString(
                                    '<div style="padding:10px 14px;background:#f0fdfa;border-left:4px solid #0d9488;border-radius:8px;">'
                                    . '<div style="font-size:11px;text-transform:uppercase;color:#0d9488;font-weight:700;letter-spacing:0.05em;">Visita rápida</div>'
                                    . '<div style="font-size:15px;font-weight:700;color:#0f172a;margin-top:2px;">'
                                    . e($record->patient->first_name . ' ' . $record->patient->last_name)
                                    . '</div>'
                                    . '<div style="font-size:12px;color:#64748b;margin-top:2px;">'
                                    . e($record->service?->name ?? 'Sin servicio') . ' · ' . $record->starts_at->format('d/m/Y H:i')
                                    . '</div>'
                                    . '</div>'
                                )),
                            // La nota en 30 segundos: un toque por frase.
                            Forms\Components\CheckboxList::make('frases')
                                ->label('¿Qué se hizo?')
                                ->options(fn () => array_merge(
                                    ($previa = \App\Support\NotaRapida::laVezPasada($record))
                                        ? ['previa' => 'Igual que la vez pasada: ' . \Illuminate\Support\Str::limit($previa, 60)]
                                        : [],
                                    array_combine(\App\Support\NotaRapida::FRASES, \App\Support\NotaRapida::FRASES),
                                ))
                                ->columns(2)
                                ->gridDirection('row'),
                            Forms\Components\Textarea::make('note')
                                ->label('Algo más (opcional)')
                                ->placeholder('Ej. Viene en 4 semanas.')
                                ->rows(2)
                                ->maxLength(500)
                                ->helperText('Todo se guarda en el expediente.'),
                            Forms\Components\Section::make('Próxima cita')
                                ->description(fn () => \App\Services\AppointmentPatternService::suggestNextDate($record->patient_id, $record->clinic_id)
                                    ? 'Pre-sugerimos la fecha basados en el patrón de visitas anteriores de este paciente.'
                                    : 'Sin patrón previo — capture la fecha si quiere agendar.')
                                ->schema([
                                    Forms\Components\DateTimePicker::make('next_appointment_date')
                                        ->label('Fecha y hora')
                                        ->seconds(false)
                                        ->minutesStep(15)
                                        ->native(false)
                                        ->displayFormat('d/m/Y H:i'),
                                    Forms\Components\Select::make('next_service_id')
                                        ->label('Servicio para la próxima cita')
                                        ->options(\App\Models\Service::where('clinic_id', auth()->user()->clinic_id)
                                            ->where('is_active', true)
                                            ->pluck('name', 'id'))
                                        ->searchable(),
                                ])
                                ->columns(2)
                                ->collapsible(),
                            Forms\Components\Section::make('Cobro')
                                ->description('Solo marque si esta visita NO está pre-pagada (ej. ortodoncia mensualidad ya cobrada).')
                                ->schema([
                                    Forms\Components\Toggle::make('charge')
                                        ->label('Registrar cobro de esta visita')
                                        ->live(),
                                    Forms\Components\TextInput::make('amount')
                                        ->label('Monto')
                                        ->numeric()
                                        ->prefix('$')
                                        ->visible(fn (Forms\Get $get) => $get('charge')),
                                    Forms\Components\Select::make('payment_method')
                                        ->label('Método de pago')
                                        ->options([
                                            'cash' => 'Efectivo',
                                            'card' => 'Tarjeta',
                                            'transfer' => 'Transferencia',
                                        ])
                                        ->visible(fn (Forms\Get $get) => $get('charge')),
                                ])
                                ->columns(2)
                                ->collapsible()
                                ->collapsed(),
                        ])
                        ->modalHeading('Visita rápida')
                        ->modalSubmitActionLabel('Registrar y cerrar')
                        ->modalWidth('xl')
                        ->action(function (Appointment $record, array $data) {
                            $nota = '';
                            foreach ($data['frases'] ?? [] as $frase) {
                                $frase = $frase === 'previa' ? \App\Support\NotaRapida::laVezPasada($record) : (in_array($frase, \App\Support\NotaRapida::FRASES, true) ? $frase : null);
                                if ($frase) {
                                    $nota = \App\Support\NotaRapida::agregar($nota, $frase);
                                }
                            }
                            if (filled($data['note'] ?? null)) {
                                $nota = \App\Support\NotaRapida::agregar($nota, $data['note']);
                            }
                            $data['note'] = $nota;

                            \App\Services\AppointmentPatternService::executeQuickVisit($record, $data);

                            \Filament\Notifications\Notification::make()
                                ->title('Visita registrada')
                                ->body('La cita se marcó como completada' . (! empty($data['next_appointment_date']) ? ' y se agendó la siguiente.' : '.'))
                                ->success()
                                ->send();
                        }),
                // Cobrar la cita, una sola vez: si ya se pagó no aparece, y si
                // quedó por cobrar (la consulta que se cerró sin ver el cobro)
                // se cobra ese mismo cobro en vez de crear otro.
                Tables\Actions\Action::make('charge')
                    ->label('Cobrar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['completed', 'in_progress'])
                        && ! ($record->payments()->exists() && ! $record->payments()->whereIn('status', ['pending', 'partial'])->exists()))
                    ->fillForm(fn (Appointment $record) => [
                        'amount' => ($debe = self::porCobrarDeLaCita($record)->sum(fn ($p) => $p->remaining))
                            ? round($debe, 2)
                            : $record->service?->price,
                        'payment_method' => 'cash',
                        'como' => 'todo',
                        'otra_forma' => 'card',
                    ])
                    // Igual que "Cobrar" de la ficha: paga todo o una parte, y
                    // en efectivo, con tarjeta o combinado (10-oct-2026).
                    // Antes siempre cobraba todo de una forma.
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Total')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->live(onBlur: true)
                            ->minValue(0.01),
                        ...\App\Filament\Doctor\Actions\Cobro::camposDePago(conSinPago: false),
                    ])
                    ->action(function (Appointment $record, array $data) {
                        $pendientes = self::porCobrarDeLaCita($record);

                        try {
                            if ($pendientes->isNotEmpty()) {
                                $hoy = \App\Filament\Doctor\Actions\Cobro::pagaHoy($data['como'] ?? 'todo', (float) $data['amount'], (float) ($data['recibido'] ?? 0));
                                \App\Filament\Doctor\Actions\Cobro::abonar($pendientes, $hoy, $data);

                                return;
                            }

                            \App\Filament\Doctor\Actions\Cobro::nuevo($data + ['service_id' => $record->service_id], $record->patient_id, $record->id);
                        } catch (\InvalidArgumentException $e) {
                            Notification::make()->title('No se registró')->body($e->getMessage())->warning()->send();
                        }
                    }),
                Tables\Actions\Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed', 'in_progress']))
                    ->action(fn (Appointment $record) => $record->update(['status' => 'completed'])),

                Tables\Actions\Action::make('reschedule')
                    ->label('Re-agendar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed']))
                    ->form([
                        Forms\Components\DateTimePicker::make('new_date')
                            ->label('Nueva fecha y hora')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->minutesStep(15)
                            ->default(fn (Appointment $record) => $record->starts_at),
                    ])
                    ->action(function (Appointment $record, array $data) {
                        $duration = $record->starts_at->diffInMinutes($record->ends_at);
                        $newStart = \Carbon\Carbon::parse($data['new_date']);
                        $newEnd = $newStart->copy()->addMinutes($duration);

                        // Por aquí no pasaba el formulario: re-agendar encimaba
                        // dos citas y se guardaba sin decir nada. Es la misma
                        // puerta que ya se cerró en el form y en el calendario.
                        $choque = Appointment::mensajeDeTraslape(
                            $record->clinic_id,
                            $record->doctor_id,
                            $newStart,
                            $newEnd,
                            $record->id,
                        );

                        if ($choque) {
                            Notification::make()
                                ->title('No se movió la cita')
                                ->body($choque)
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->update([
                            'starts_at' => $newStart,
                            'ends_at' => $newEnd,
                        ]);

                        $movida = $record->fresh();

                        // Cabe, pero quizá quedó sin tiempo de limpiar.
                        $pegada = Appointment::avisoDeEspacio(
                            $record->clinic_id,
                            $record->doctor_id,
                            $newStart,
                            $newEnd,
                            $record->id,
                        );

                        Notification::make()
                            ->title('Cita movida al ' . $newStart->format('d/m/Y H:i'))
                            ->body($pegada ?? '')
                            ->color($pegada ? 'warning' : 'success')
                            ->send();

                        if ($movida->seHaMovidoDeMas()) {
                            Notification::make()
                                ->title('Esta cita ya se movió ' . $movida->veces_reagendada . ' veces')
                                ->body('Conviene confirmársela por WhatsApp antes de apartarle el lugar.')
                                ->warning()
                                ->persistent()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar cita')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Appointment $record) => in_array($record->status, ['scheduled', 'confirmed']))
                    ->action(function (Appointment $record) {
                        $record->update(['status' => 'cancelled']);
                        self::avisarDelHueco($record);
                    }),
                    Tables\Actions\EditAction::make()->label('Editar detalles'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            // Sin borrar en bloque: se llevaba procedimientos y dejaba cobros
            // sueltos, y la cita futura se iba sin pasar por "Cancelar" (que
            // le ofrece el hueco a la lista de espera). Auditoría del 12-oct.
            ->bulkActions([])
            // Sin esto Filament dice "No se encontraron registros", que no
            // le dice al doctor que hacer ni con que llenarlo.
            ->emptyStateHeading('Su agenda está vacía')
            ->emptyStateDescription('Cree su primera cita: elige paciente, día y servicio, y la duración se calcula sola.')
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('starts_at', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }

    /**
     * El aviso al cancelar: quién de la lista de espera cabe en el hueco, con
     * "Ofrecer a ..." para cada uno. Lo usan la lista de citas y la cita abierta.
     */
    public static function avisarDelHueco(Appointment $record): void
    {
        // Al cancelar queda un hueco. En vez de esperar a que
        // el doctor se acuerde de a quien le urgia esa fecha,
        // le decimos quien de la lista de espera cabe ahi.
        $candidatos = \App\Models\WaitlistEntry::candidatosPara($record);

        if ($candidatos->isEmpty()) {
            Notification::make()
                ->title('Cita cancelada')
                ->body('Nadie de su lista de espera pedía ese día.')
                ->success()
                ->send();

            return;
        }

        $lista = $candidatos
            ->map(function ($entrada) {
                $nombre = trim(($entrada->patient->first_name ?? '') . ' ' . ($entrada->patient->last_name ?? ''));
                $urgente = (int) $entrada->priority === 1 ? ' — urgente' : '';

                return '• ' . e($nombre . $urgente);
            })
            // Uno por renglón: con "\n" el aviso los juntaba.
            ->implode('<br>');

        // "Ofrecer a Diego" abre WhatsApp con el mensaje y lo
        // deja anotado para este hueco: sin pasar por la lista.
        Notification::make()
            ->title('Se liberó el ' . $record->starts_at->translatedFormat('l d \d\e F, H:i'))
            ->body(new \Illuminate\Support\HtmlString('Estos pacientes esperaban ese día:<br>' . $lista))
            ->info()
            ->persistent()
            ->actions(\App\Models\WaitlistEntry::botonesParaElHueco($record, $candidatos))
            ->send();
    }
}
