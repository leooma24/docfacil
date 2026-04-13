<?php

namespace App\Filament\Sales\Resources;

use App\Filament\Sales\Resources\ProspectResource\Pages;
use App\Models\Prospect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProspectResource extends Resource
{
    protected static ?string $model = Prospect::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationLabel = 'Mis Prospectos';

    protected static ?string $modelLabel = 'Prospecto';

    protected static ?string $pluralModelLabel = 'Prospectos';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('assigned_to_sales_rep_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del prospecto')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                    Forms\Components\TextInput::make('clinic_name')->label('Consultorio')->maxLength(255),
                    Forms\Components\TextInput::make('phone')->label('Teléfono')->tel()->required(),
                    Forms\Components\TextInput::make('email')->label('Email')->email(),
                    Forms\Components\TextInput::make('city')->label('Ciudad'),
                    Forms\Components\TextInput::make('specialty')->label('Especialidad'),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'new' => 'Nuevo',
                            'contacted' => 'Contactado',
                            'interested' => 'Interesado',
                            'trial' => 'En trial',
                            'lost' => 'Perdido',
                        ])
                        ->default('new'),
                    Forms\Components\DateTimePicker::make('next_followup_at')
                        ->label('Siguiente seguimiento'),
                    Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull()->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable()
                    ->description(fn (Prospect $r) => $r->clinic_name ?: null),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono')->searchable(),
                Tables\Columns\TextColumn::make('specialty')->label('Especialidad')
                    ->toggleable()->badge()->color('gray'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'new',
                        'info' => 'contacted',
                        'warning' => 'interested',
                        'primary' => 'trial',
                        'success' => 'converted',
                        'danger' => 'lost',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'new' => 'Nuevo',
                        'contacted' => 'Contactado',
                        'interested' => 'Interesado',
                        'trial' => 'En trial',
                        'converted' => 'Convertido ✓',
                        'lost' => 'Perdido',
                    }),
                Tables\Columns\TextColumn::make('contact_day')
                    ->label('Cadencia')
                    ->formatStateUsing(function ($state, Prospect $r) {
                        if ($r->status === 'converted') return '✅ Cerrado';
                        if ($r->status === 'lost') return '❌';
                        if ($state == 0) return '⬜ Sin iniciar';
                        $steps = [1 => '1️⃣', 3 => '2️⃣', 7 => '3️⃣', 14 => '4️⃣', 30 => '5️⃣'];
                        $icon = $steps[$state] ?? '🔄';
                        return "{$icon} Día {$state}";
                    })
                    ->description(function (Prospect $r) {
                        if (!$r->next_contact_at) return null;
                        if ($r->next_contact_at->isPast()) return '⚠️ Seguimiento pendiente';
                        return 'Próximo: ' . $r->next_contact_at->format('d/m');
                    })
                    ->color(fn (Prospect $r) => $r->next_contact_at?->isPast() ? 'danger' : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_contact_method')
                    ->label('Último medio')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'whatsapp' => '💬 WhatsApp',
                        'email' => '📧 Email',
                        'phone' => '📞 Llamada',
                        'in_person' => '🤝 Presencial',
                        'demo' => '💻 Demo',
                        default => '—',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')->label('Ciudad')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Registrado')->date('d/m/Y')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'new' => 'Nuevo',
                        'contacted' => 'Contactado',
                        'interested' => 'Interesado',
                        'trial' => 'En trial',
                        'converted' => 'Convertido',
                        'lost' => 'Perdido',
                    ]),
                Tables\Filters\Filter::make('pending_contact')
                    ->label('Seguimiento pendiente')
                    ->query(fn ($q) => $q->where(function ($q) {
                        $q->whereNotNull('next_contact_at')->where('next_contact_at', '<=', now());
                    })->orWhere(function ($q) {
                        $q->where('contact_day', 0)->where('status', 'new');
                    })),
                Tables\Filters\Filter::make('no_started')
                    ->label('Sin iniciar cadencia')
                    ->query(fn ($q) => $q->where('contact_day', 0)->whereIn('status', ['new', 'contacted'])),
            ])
            ->actions([
                // Botón principal: avanzar cadencia con selección de método
                Tables\Actions\Action::make('advance_contact')
                    ->label(fn (Prospect $r) => $r->contact_day == 0 ? 'Iniciar contacto' : 'Registrar contacto')
                    ->icon(fn (Prospect $r) => $r->contact_day == 0 ? 'heroicon-o-play' : 'heroicon-o-arrow-right')
                    ->color(fn (Prospect $r) => $r->next_contact_at?->isPast() ? 'danger' : 'primary')
                    ->visible(fn (Prospect $r) => !in_array($r->status, ['converted', 'lost']))
                    ->form([
                        Forms\Components\Select::make('method')
                            ->label('¿Cómo lo contactaste?')
                            ->options([
                                'whatsapp' => '💬 WhatsApp',
                                'phone' => '📞 Llamada',
                                'in_person' => '🤝 Presencial',
                                'email' => '📧 Email',
                                'demo' => '💻 Demo en vivo',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('contact_notes')
                            ->label('¿Qué pasó? (opcional)')
                            ->rows(2)
                            ->placeholder('Ej: Le interesó, quiere demo el jueves...'),
                    ])
                    ->action(function (Prospect $record, array $data) {
                        $record->advanceContactDay($data['method']);

                        // Update status if still new
                        if ($record->status === 'new') {
                            $record->update([
                                'status' => 'contacted',
                                'contacted_at' => $record->contacted_at ?? now(),
                            ]);
                        }

                        // Append notes if provided
                        if (!empty($data['contact_notes'])) {
                            $dateStr = now()->format('d/m H:i');
                            $methodLabel = match ($data['method']) {
                                'whatsapp' => 'WhatsApp', 'phone' => 'Llamada',
                                'in_person' => 'Presencial', 'email' => 'Email', 'demo' => 'Demo',
                            };
                            $note = "[{$dateStr} · {$methodLabel}] {$data['contact_notes']}";
                            $record->update([
                                'notes' => trim(($record->notes ? $record->notes . "\n" : '') . $note),
                            ]);
                        }

                        $nextDay = Prospect::CADENCE[$record->contact_day] ?? null;
                        $msg = $nextDay
                            ? "Contacto Día {$record->contact_day} registrado. Próximo: Día {$nextDay}"
                            : "Cadencia completada para este prospecto";

                        Notification::make()->title($msg)->success()->send();
                    }),

                // WhatsApp con mensaje pre-armado según el día de cadencia
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (Prospect $r) => !empty($r->phone) && !in_array($r->status, ['converted', 'lost']))
                    ->url(function (Prospect $record) {
                        $phone = preg_replace('/[\s\-\(\)\+]/', '', $record->phone);
                        if (strlen($phone) === 10) $phone = '52' . $phone;

                        $name = explode(' ', trim($record->name))[0] ?? '';
                        $isDentist = str_contains(strtolower($record->specialty ?? ''), 'dent')
                            || str_contains(strtolower($record->specialty ?? ''), 'odont');

                        $msg = match ($record->contact_day) {
                            0, 1 => "Hola {$name}, buenas tardes. Soy de DocFacil. Vi que tiene su consultorio"
                                . ($record->city ? " en {$record->city}" : '') . " y queria preguntarle: como lleva el control de citas y expedientes?"
                                . " Le pregunto porque estamos ayudando a " . ($isDentist ? 'dentistas' : 'doctores')
                                . " a recuperar citas perdidas con recordatorios automaticos por WhatsApp."
                                . " Se lo puedo mostrar en 10 min. Que dia le queda bien?",
                            3 => "Hola {$name}, le doy seguimiento rapido. "
                                . ($isDentist ? "Los dentistas" : "Los doctores") . " que ya usan DocFacil recuperan entre 8 y 12 citas al mes con recordatorios WhatsApp automaticos."
                                . " Si cada cita vale \$500, son \$4,000+ mas al mes. El sistema cuesta \$149."
                                . " Le puedo hacer una demo de 10 min. Le interesa?",
                            7 => "{$name}, ultimo mensaje, no quiero ser molesto. Solo le dejo el acceso gratuito para que lo pruebe por su cuenta: https://docfacil.tu-app.co/doctor/register"
                                . " Si en algun momento quiere platicarlo, aqui estoy. Que tenga excelente dia.",
                            14 => "Hola {$name}, hace unas semanas le platique de DocFacil. Desde entonces agregamos mas funciones y ya tenemos varios "
                                . ($isDentist ? 'consultorios dentales' : 'consultorios') . " usandolo en la zona."
                                . " Si sigue con el pendiente de organizar citas y expedientes, sigo disponible para una demo rapida.",
                            default => "Hola {$name}, soy de DocFacil. Queria saber si le interesa conocer nuestro software para su consultorio."
                                . " Agenda, expedientes, recetas PDF y recordatorios por WhatsApp. Todo en un solo lugar.",
                        };

                        return "https://wa.me/{$phone}?text=" . urlencode($msg);
                    })
                    ->openUrlInNewTab(),

                // Ver objeciones comunes
                Tables\Actions\Action::make('objections')
                    ->label('Objeciones')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning')
                    ->modalHeading('Respuestas a objeciones comunes')
                    ->modalDescription('Copia la respuesta que necesites. Tono natural, no vendedor.')
                    ->modalSubmitAction(false)
                    ->modalContent(view('filament.sales.objections-modal')),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\BulkAction::make('start_cadence')
                    ->label('Iniciar cadencia')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function ($records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->contact_day == 0 && !in_array($record->status, ['converted', 'lost'])) {
                                $record->update([
                                    'contact_day' => 1,
                                    'outreach_started_at' => now(),
                                    'next_contact_at' => now()->addDays(2),
                                ]);
                                $count++;
                            }
                        }
                        Notification::make()->title("Cadencia iniciada para {$count} prospectos")->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProspects::route('/'),
            'create' => Pages\CreateProspect::route('/create'),
            'edit' => Pages\EditProspect::route('/{record}/edit'),
        ];
    }
}
