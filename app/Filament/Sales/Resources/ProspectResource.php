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
                            'interested' => 'En seguimiento',
                            'trial' => 'En trial',
                            'lost' => 'Perdido',
                        ])
                        ->default('new'),
                    Forms\Components\DateTimePicker::make('next_followup_at')
                        ->label('Siguiente seguimiento'),
                    Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull()->rows(3),
                ]),
            Forms\Components\Section::make('Seguimiento de venta')
                ->columns(2)
                ->collapsed()
                ->schema([
                    Forms\Components\DateTimePicker::make('demo_scheduled_at')
                        ->label('Demo agendada'),
                    Forms\Components\DateTimePicker::make('demo_completed_at')
                        ->label('Demo realizada'),
                    Forms\Components\CheckboxList::make('objections_faced')
                        ->label('Objeciones que puso')
                        ->options(\App\Models\Prospect::OBJECTION_CATALOG)
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Prospecto')->searchable()->sortable()
                    ->description(fn (Prospect $r) => collect([$r->specialty, $r->clinic_name, $r->city])->filter()->implode(' · ') ?: null),
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
                        'interested' => 'En seguimiento',
                        'trial' => 'En trial',
                        'converted' => 'Convertido ✓',
                        'lost' => 'Perdido',
                    }),
                Tables\Columns\TextColumn::make('contact_day')
                    ->label('Seguimiento')
                    ->formatStateUsing(function ($state, Prospect $r) {
                        if ($r->status === 'converted') return '✅';
                        if ($r->status === 'lost') return '—';
                        if ($state == 0) return 'Sin iniciar';
                        return "Día {$state}";
                    })
                    ->description(function (Prospect $r) {
                        if ($r->status === 'converted' || $r->status === 'lost') return null;
                        if (!$r->next_contact_at) return $r->last_contact_method ? match ($r->last_contact_method) {
                            'whatsapp' => '💬', 'phone' => '📞', 'in_person' => '🤝', 'email' => '📧', 'demo' => '💻', default => '',
                        } . ' último contacto' : null;
                        if ($r->next_contact_at->isPast()) return '⚠️ Pendiente';
                        return '→ ' . $r->next_contact_at->format('d/m');
                    })
                    ->color(fn (Prospect $r) => $r->next_contact_at?->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('email_events_count')
                    ->label('Engagement')
                    ->counts('emailEvents')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "🔥 {$state} click" . ($state > 1 ? 's' : '') : '—')
                    ->color(fn ($state) => $state >= 2 ? 'success' : ($state >= 1 ? 'warning' : 'gray'))
                    ->sortable()
                    ->tooltip('Clicks del prospect en links de los correos enviados'),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono')->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->date('d/m')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'new' => 'Nuevo',
                        'contacted' => 'Contactado',
                        'interested' => 'En seguimiento',
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
                // ═══ BOTÓN 1: Acción principal (cambia según estado) ═══
                Tables\Actions\Action::make('main_action')
                    ->label(fn (Prospect $r) => match ($r->status) {
                        'new' => $r->contact_day == 0 ? '▶ Iniciar contacto' : '▶ Registrar contacto',
                        'contacted' => '▶ Marcar en seguimiento',
                        'interested' => '▶ Enviar link registro',
                        'trial' => '✓ Marcar convertido',
                        default => 'Acción',
                    })
                    ->icon(fn (Prospect $r) => match ($r->status) {
                        'new' => 'heroicon-o-play',
                        'contacted' => 'heroicon-o-chevron-double-right',
                        'interested' => 'heroicon-o-link',
                        'trial' => 'heroicon-o-check-badge',
                        default => 'heroicon-o-play',
                    })
                    ->color(fn (Prospect $r) => match (true) {
                        $r->next_contact_at?->isPast() ?? false => 'danger',
                        $r->status === 'trial' => 'success',
                        default => 'primary',
                    })
                    ->visible(fn (Prospect $r) => !in_array($r->status, ['converted', 'lost']))
                    ->form(fn (Prospect $r) => match ($r->status) {
                        'new' => [
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
                        ],
                        'trial' => [
                            Forms\Components\Placeholder::make('confirm')
                                ->content('¿El prospecto se convirtió en cliente? Esto genera tu comisión.'),
                        ],
                        default => [],
                    })
                    ->requiresConfirmation(fn (Prospect $r) => in_array($r->status, ['contacted', 'trial']))
                    ->modalDescription(fn (Prospect $r) => match ($r->status) {
                        'contacted' => '¿El prospecto mostró interés real (quiere demo, pidió info, etc)?',
                        'trial' => '¿El prospecto pagó y se convirtió en cliente?',
                        default => null,
                    })
                    ->action(function (Prospect $record, array $data) {
                        // URL de WhatsApp opcional. Si se setea, retornamos redirect()
                        // al final del action para que el navegador abra wa.me en
                        // pestana nueva sin que el rep tenga que copiar/pegar.
                        $whatsappRedirect = null;

                        switch ($record->status) {
                            case 'new':
                                $record->advanceContactDay($data['method']);
                                $record->update([
                                    'status' => 'contacted',
                                    'contacted_at' => $record->contacted_at ?? now(),
                                ]);
                                if (!empty($data['contact_notes'])) {
                                    $dateStr = now()->format('d/m H:i');
                                    $methodLabel = match ($data['method']) {
                                        'whatsapp' => 'WhatsApp', 'phone' => 'Llamada',
                                        'in_person' => 'Presencial', 'email' => 'Email', 'demo' => 'Demo',
                                    };
                                    $record->update([
                                        'notes' => trim(($record->notes ? $record->notes . "\n" : '') . "[{$dateStr} · {$methodLabel}] {$data['contact_notes']}"),
                                    ]);
                                }
                                // Si eligio WhatsApp como metodo, abrir wa.me con mensaje de intro
                                if ($data['method'] === 'whatsapp' && !empty($record->phone)) {
                                    $whatsappRedirect = static::buildIntroWhatsappUrl($record);
                                }
                                Notification::make()
                                    ->title($whatsappRedirect ? 'Status actualizado · abriendo WhatsApp...' : 'Contacto registrado. Próximo seguimiento agendado.')
                                    ->success()->send();
                                break;

                            case 'contacted':
                                $record->update(['status' => 'interested']);
                                Notification::make()->title('Prospecto marcado como interesado')->success()->send();
                                break;

                            case 'interested':
                                $record->update(['status' => 'trial']);
                                if (!empty($record->phone)) {
                                    $whatsappRedirect = static::buildRegisterLinkWhatsappUrl($record);
                                }
                                Notification::make()
                                    ->title($whatsappRedirect ? 'Movido a trial · abriendo WhatsApp con el link' : 'Movido a trial')
                                    ->success()->send();
                                break;

                            case 'trial':
                                $record->update(['status' => 'converted', 'converted_at' => now()]);
                                Notification::make()->title('¡Convertido! Tu comisión se genera automáticamente.')->success()->send();
                                break;
                        }

                        if ($whatsappRedirect) {
                            return redirect()->away($whatsappRedirect);
                        }
                    }),

                // ═══ BOTÓN 2: WhatsApp (siempre visible) ═══
                Tables\Actions\Action::make('whatsapp')
                    ->label('')
                    ->tooltip('WhatsApp con mensaje listo')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (Prospect $r) => !empty($r->phone) && !in_array($r->status, ['converted', 'lost']))
                    ->url(function (Prospect $record) {
                        $phone = preg_replace('/[\s\-\(\)\+]/', '', $record->phone);
                        if (strlen($phone) === 10) $phone = '52' . $phone;
                        $name = $record->firstName();
                        $isDentist = str_contains(strtolower($record->specialty ?? ''), 'dent')
                            || str_contains(strtolower($record->specialty ?? ''), 'odont');

                        $msg = match ($record->contact_day) {
                            0, 1 => "Hola {$name}, soy de DocFacil. Vi su consultorio" . ($record->city ? " en {$record->city}" : '')
                                . " y queria preguntarle: como lleva el control de citas? Estamos ayudando a " . ($isDentist ? 'dentistas' : 'doctores')
                                . " a recuperar citas perdidas con recordatorios WhatsApp. Se lo muestro en 10 min?",
                            3 => "Hola {$name}, le doy seguimiento. " . ($isDentist ? "Dentistas" : "Doctores")
                                . " que usan DocFacil recuperan 8-12 citas/mes. Son \$4,800+ extra por \$499/mes y garantia de 30 dias. Le hago una demo?",
                            7 => "{$name}, ultimo mensaje. Le dejo acceso gratuito: https://docfacil.tu-app.co/doctor/register — Aqui estoy si lo necesita.",
                            14 => "Hola {$name}, seguimos mejorando DocFacil. Si sigue con el pendiente de organizar su consultorio, sigo disponible.",
                            default => "Hola {$name}, soy de DocFacil. Agenda, expedientes, recetas PDF y WhatsApp auto + 1 clic. Todo en uno. Le interesa una demo?",
                        };
                        return "https://wa.me/{$phone}?text=" . urlencode($msg);
                    })
                    ->openUrlInNewTab(),

                // ═══ BOTÓN 3: Menú "Más" con todo lo demás ═══
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('advance_contact')
                        ->label('Registrar contacto')
                        ->icon('heroicon-o-phone-arrow-up-right')
                        ->visible(fn (Prospect $r) => $r->status === 'contacted' && !in_array($r->status, ['converted', 'lost']))
                        ->form([
                            Forms\Components\Select::make('method')
                                ->label('¿Cómo lo contactaste?')
                                ->options([
                                    'whatsapp' => '💬 WhatsApp', 'phone' => '📞 Llamada',
                                    'in_person' => '🤝 Presencial', 'email' => '📧 Email', 'demo' => '💻 Demo',
                                ])->required(),
                            Forms\Components\Textarea::make('contact_notes')
                                ->label('¿Qué pasó?')->rows(2),
                        ])
                        ->action(function (Prospect $record, array $data) {
                            $record->advanceContactDay($data['method']);
                            if (!empty($data['contact_notes'])) {
                                $record->update(['notes' => trim(($record->notes ? $record->notes . "\n" : '') . '[' . now()->format('d/m H:i') . '] ' . $data['contact_notes'])]);
                            }
                            Notification::make()->title("Contacto D{$record->contact_day} registrado")->success()->send();
                        }),

                    Tables\Actions\Action::make('schedule_demo')
                        ->label('Agendar demo')
                        ->icon('heroicon-o-computer-desktop')
                        ->visible(fn (Prospect $r) => !$r->demo_completed_at && !in_array($r->status, ['converted', 'lost']))
                        ->form([
                            Forms\Components\DateTimePicker::make('demo_at')
                                ->label('¿Cuándo?')->required()
                                ->default(now()->addDay()->setHour(13)->setMinute(0)),
                        ])
                        ->action(function (Prospect $record, array $data) {
                            $record->update(['demo_scheduled_at' => $data['demo_at']]);
                            Notification::make()->title('Demo agendada')->success()->send();
                        }),

                    Tables\Actions\Action::make('proposal')
                        ->label('Generar propuesta PDF')
                        ->icon('heroicon-o-document-text')
                        ->visible(fn (Prospect $r) => in_array($r->status, ['interested', 'trial']))
                        ->url(fn (Prospect $r) => route('sales.proposal.pdf', $r))
                        ->openUrlInNewTab(),

                    Tables\Actions\Action::make('log_objection')
                        ->label('Registrar objeciones')
                        ->icon('heroicon-o-shield-exclamation')
                        ->visible(fn (Prospect $r) => !in_array($r->status, ['converted', 'lost']))
                        ->form([
                            Forms\Components\CheckboxList::make('objections')
                                ->label('¿Qué objeciones puso?')
                                ->options(\App\Models\Prospect::OBJECTION_CATALOG)
                                ->columns(2),
                        ])
                        ->action(function (Prospect $record, array $data) {
                            $existing = $record->objections_faced ?? [];
                            $merged = array_unique(array_merge($existing, $data['objections'] ?? []));
                            $record->update(['objections_faced' => array_values($merged)]);
                            Notification::make()->title('Objeciones registradas')->success()->send();
                        }),

                    Tables\Actions\Action::make('objections_help')
                        ->label('Ver respuestas a objeciones')
                        ->icon('heroicon-o-light-bulb')
                        ->modalHeading('Respuestas a objeciones')
                        ->modalSubmitAction(false)
                        ->modalContent(view('filament.sales.objections-modal')),

                    Tables\Actions\Action::make('mark_lost')
                        ->label('Marcar como perdido')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Prospect $r) => !in_array($r->status, ['converted', 'lost']))
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Textarea::make('lost_reason')
                                ->label('¿Por qué?')->rows(2),
                        ])
                        ->action(function (Prospect $record, array $data) {
                            $update = ['status' => 'lost'];
                            if (!empty($data['lost_reason'])) {
                                $update['notes'] = trim(($record->notes ? $record->notes . "\n" : '') . '[' . now()->format('d/m') . ' · PERDIDO] ' . $data['lost_reason']);
                            }
                            $record->update($update);
                            Notification::make()->title('Prospecto marcado como perdido')->warning()->send();
                        }),

                    Tables\Actions\EditAction::make()->label('Editar datos'),
                ])
                    ->label('')
                    ->tooltip('Más opciones')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->color('gray'),
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

    /**
     * URL wa.me con mensaje de intro cuando el rep marca primer contacto
     * por WhatsApp. Tono humano, no spam, opcion de salir abierta.
     */
    protected static function buildIntroWhatsappUrl(Prospect $record): string
    {
        $phone = preg_replace('/\D/', '', (string) $record->phone);
        if (strlen($phone) === 10) $phone = '52' . $phone;

        $name = $record->firstName();
        $cityPart = $record->city ? " en {$record->city}" : '';

        $msg = "Hola Dr. {$name},\n\n"
            . "Soy Omar de DocFácil. Vi su consultorio{$cityPart} y le escribo breve — sé que su tiempo vale.\n\n"
            . "Es un sistema mexicano que ayuda a consultorios a recuperar las citas que no llegan (1 de cada 3 pacientes no llega = $500-1500 perdidos cada uno).\n\n"
            . "Si tiene 30 segundos, ¿le mando una liga rápida? Si no le late, me avisa y lo dejo en paz.";

        return "https://wa.me/{$phone}?text=" . urlencode($msg);
    }

    /**
     * URL wa.me con link de registro cuando el rep avanza a 'trial' (el
     * prospecto pidió la liga). Incluye codigo de vendedor para atribucion
     * de comision si convierte.
     */
    protected static function buildRegisterLinkWhatsappUrl(Prospect $record): string
    {
        $phone = preg_replace('/\D/', '', (string) $record->phone);
        if (strlen($phone) === 10) $phone = '52' . $phone;

        $code = auth()->user()->sales_rep_code ?? '';
        $name = $record->firstName();

        $url = url('/doctor/register') . ($code ? "?vnd={$code}" : '');
        $msg = "Hola {$name}, aquí le dejo su acceso a DocFácil para que lo pruebe gratis 15 días:\n\n"
            . "{$url}\n\n"
            . "Se registra en 2 minutos. Cualquier duda me dice por aquí.";

        return "https://wa.me/{$phone}?text=" . urlencode($msg);
    }
}
