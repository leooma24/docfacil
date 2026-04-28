<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProspectResource\Pages;
use App\Models\Prospect;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProspectResource extends Resource
{
    protected static ?string $model = Prospect::class;

    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    protected static ?string $navigationLabel = 'Prospectos';

    protected static ?string $modelLabel = 'Prospecto';

    protected static ?string $pluralModelLabel = 'Prospectos';

    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Prospecto')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Nombre')->required(),
                        Forms\Components\TextInput::make('email')->label('Email')->email()->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')->label('Teléfono')->tel(),
                        Forms\Components\TextInput::make('clinic_name')->label('Nombre del consultorio'),
                        Forms\Components\TextInput::make('city')->label('Ciudad'),
                        Forms\Components\TextInput::make('specialty')->label('Especialidad'),
                        Forms\Components\Select::make('source')
                            ->label('Fuente')
                            ->options([
                                'landing' => 'Landing page',
                                'referral' => 'Referido',
                                'google' => 'Google',
                                'social' => 'Redes sociales',
                                'prospecting' => 'Prospección',
                                'other' => 'Otro',
                            ])
                            ->default('landing'),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'new' => 'Nuevo',
                                'contacted' => 'Contactado',
                                'interested' => 'En seguimiento',
                                'trial' => 'En trial',
                                'converted' => 'Convertido',
                                'lost' => 'Perdido',
                            ])
                            ->default('new')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'contacted') {
                                    $set('contacted_at', now()->toDateTimeString());
                                }
                                if ($state === 'converted') {
                                    $set('converted_at', now()->toDateTimeString());
                                }
                            }),
                        Forms\Components\DateTimePicker::make('contacted_at')->label('Fecha contacto'),
                        Forms\Components\DateTimePicker::make('converted_at')->label('Fecha conversión'),
                        Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull()->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable()
                    ->icon(fn ($state) => $state ? 'heroicon-o-envelope' : null)
                    ->placeholder('Sin email'),
                Tables\Columns\TextColumn::make('phone')->label('Teléfono')->searchable()
                    ->icon(fn ($state) => $state ? 'heroicon-o-phone' : null)
                    ->placeholder('Sin teléfono'),
                Tables\Columns\TextColumn::make('clinic_name')->label('Consultorio')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('city')->label('Ciudad')->sortable()->toggleable(),
                Tables\Columns\BadgeColumn::make('assignedSalesRep.name')
                    ->label('Asignado a')
                    ->placeholder('Sin asignar')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-user' : 'heroicon-o-user-minus')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('specialty')->label('Especialidad')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('source')
                    ->label('Fuente')
                    ->colors([
                        'primary' => 'landing',
                        'success' => 'referral',
                        'info' => 'google',
                        'warning' => 'social',
                        'danger' => 'prospecting',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'landing' => 'Landing page',
                        'referral' => 'Referido',
                        'google' => 'Google',
                        'social' => 'Redes sociales',
                        'prospecting' => 'Prospección',
                        default => 'Otro',
                    }),
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
                        'converted' => 'Convertido',
                        'lost' => 'Perdido',
                    }),
                Tables\Columns\TextColumn::make('contacted_at')->label('Contactado')->date('d/m/Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Registrado')->date('d/m/Y')->sortable(),
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
                Tables\Filters\SelectFilter::make('source')
                    ->label('Fuente')
                    ->options([
                        'landing' => 'Landing page',
                        'referral' => 'Referido',
                        'google' => 'Google',
                        'social' => 'Redes sociales',
                        'prospecting' => 'Prospección',
                        'other' => 'Otro',
                    ]),
                Tables\Filters\SelectFilter::make('city')
                    ->label('Ciudad')
                    ->options(fn () => Prospect::whereNotNull('city')->distinct()->pluck('city', 'city')->toArray()),
                Tables\Filters\Filter::make('has_email')
                    ->label('Con email')
                    ->query(fn ($query) => $query->whereNotNull('email')),
                Tables\Filters\Filter::make('no_email')
                    ->label('Sin email (solo WhatsApp)')
                    ->query(fn ($query) => $query->whereNull('email')->whereNotNull('phone')),
                Tables\Filters\SelectFilter::make('assigned_to_sales_rep_id')
                    ->label('Asignado a')
                    ->options(fn () => User::where('role', 'sales')->pluck('name', 'id')->toArray())
                    ->placeholder('Todos'),
                Tables\Filters\Filter::make('unassigned')
                    ->label('Sin asignar')
                    ->query(fn ($query) => $query->whereNull('assigned_to_sales_rep_id')),
            ])
            ->actions([
                // WhatsApp button — opens wa.me with pre-filled beta invite message
                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->visible(fn (Prospect $record) => !empty($record->phone))
                    ->url(function (Prospect $record) {
                        $phone = preg_replace('/[\s\-\(\)\+]/', '', $record->phone);
                        if (strlen($phone) === 10) $phone = '52' . $phone;
                        $name = $record->name;
                        $clinic = $record->clinic_name ?? 'tu consultorio';

                        $status = $record->status;
                        $message = match ($status) {
                            'new' => "Hola *{$name}* 👋 Soy Omar de *DocFácil*, un software para consultorios médicos y dentales. Estamos invitando consultorios de Sinaloa a nuestro *beta gratuito* con agenda de citas, expedientes digitales, recetas PDF y recordatorios WhatsApp. ¿Te interesa probarlo sin costo para {$clinic}?",
                            'contacted' => "Hola *{$name}*, te escribí hace unos días sobre DocFácil 😊 Otros consultorios ya redujeron 50% sus citas perdidas con nuestro sistema. ¿Te gustaría una demo rápida de 5 minutos?",
                            'interested' => "Hola *{$name}*, último mensaje sobre DocFácil 🙏 Quedan pocos lugares en el beta gratuito con precio preferencial de por vida. ¿Te animas a probarlo? Es gratis.",
                            default => "Hola *{$name}*, soy Omar de DocFácil. ¿Cómo va todo con tu consultorio?",
                        };

                        return 'https://wa.me/' . $phone . '?text=' . urlencode($message);
                    }, shouldOpenInNewTab: true),

                // Advance status button
                Tables\Actions\Action::make('advance')
                    ->label(fn (Prospect $record) => match ($record->status) {
                        'new' => 'Marcar contactado',
                        'contacted' => 'Marcar interesado',
                        'interested' => 'Marcar en trial',
                        'trial' => 'Marcar convertido',
                        default => 'Avanzar',
                    })
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color(fn (Prospect $record) => match ($record->status) {
                        'new' => 'info',
                        'contacted' => 'warning',
                        'interested' => 'primary',
                        'trial' => 'success',
                        default => 'gray',
                    })
                    ->visible(fn (Prospect $record) => in_array($record->status, ['new', 'contacted', 'interested', 'trial']))
                    ->requiresConfirmation()
                    ->modalHeading(fn (Prospect $record) => "Avanzar a {$record->name}")
                    ->modalDescription(fn (Prospect $record) => match ($record->status) {
                        'new' => '¿Ya contactaste a este prospecto?',
                        'contacted' => '¿El prospecto mostró interés?',
                        'interested' => '¿El prospecto inició su trial/registro?',
                        'trial' => '¿El prospecto se convirtió en cliente?',
                        default => '¿Avanzar estado?',
                    })
                    ->action(function (Prospect $record) {
                        $nextStatus = match ($record->status) {
                            'new' => 'contacted',
                            'contacted' => 'interested',
                            'interested' => 'trial',
                            'trial' => 'converted',
                            default => $record->status,
                        };

                        $updateData = ['status' => $nextStatus];
                        if ($nextStatus === 'contacted') $updateData['contacted_at'] = now();
                        if ($nextStatus === 'converted') $updateData['converted_at'] = now();

                        $record->update($updateData);

                        Notification::make()
                            ->success()
                            ->title("{$record->name} → " . match ($nextStatus) {
                                'contacted' => 'Contactado',
                                'interested' => 'En seguimiento',
                                'trial' => 'En trial',
                                'converted' => 'Convertido',
                                default => $nextStatus,
                            })
                            ->send();
                    }),

                // Mark as lost
                Tables\Actions\Action::make('lost')
                    ->label('Perdido')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Prospect $record) => in_array($record->status, ['new', 'contacted', 'interested']))
                    ->requiresConfirmation()
                    ->modalHeading('Marcar como perdido')
                    ->action(function (Prospect $record) {
                        $record->update(['status' => 'lost']);
                        Notification::make()->warning()->title("{$record->name} marcado como perdido")->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('advance_bulk')
                    ->label('Avanzar estado')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $count = 0;
                        foreach ($records as $record) {
                            $nextStatus = match ($record->status) {
                                'new' => 'contacted',
                                'contacted' => 'interested',
                                'interested' => 'trial',
                                'trial' => 'converted',
                                default => null,
                            };
                            if ($nextStatus) {
                                $updateData = ['status' => $nextStatus];
                                if ($nextStatus === 'contacted') $updateData['contacted_at'] = now();
                                if ($nextStatus === 'converted') $updateData['converted_at'] = now();
                                $record->update($updateData);
                                $count++;
                            }
                        }
                        Notification::make()->success()->title("{$count} prospectos avanzados")->send();
                    }),
            ])
            ->defaultSort('status', 'asc');
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
