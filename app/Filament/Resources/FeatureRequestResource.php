<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureRequestResource\Pages;
use App\Models\FeatureRequest;
use App\Models\FeatureVote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Admin: revision + seleccion de ganadoras mensuales del roadmap
 * comunitario. Aqui Omar ve todas las propuestas ordenadas por
 * score monetizable (votos x precio promedio) y marca las 2 ganadoras
 * cada mes (1 paga + 1 gratis).
 */
class FeatureRequestResource extends Resource
{
    protected static ?string $model = FeatureRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationLabel = 'Roadmap comunitario';

    protected static ?string $modelLabel = 'Propuesta';

    protected static ?string $pluralModelLabel = 'Propuestas del roadmap';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Revisión de la propuesta')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('title')->label('Título')->required()->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->label('Descripción')->rows(5)->columnSpanFull(),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options(FeatureRequest::STATUSES)
                        ->required(),
                    Forms\Components\Select::make('proposed_price_tier')
                        ->label('Precio sugerido por proponente')
                        ->options(FeatureRequest::PRICE_TIERS),
                    Forms\Components\Select::make('release_type')
                        ->label('Tipo al entregar')
                        ->options(FeatureRequest::RELEASE_TYPES)
                        ->placeholder('Aún sin decidir'),
                    Forms\Components\TextInput::make('winner_month')
                        ->label('Mes ganador (YYYY-MM)')
                        ->placeholder('2026-05'),
                    Forms\Components\DateTimePicker::make('shipped_at')->label('Entregada el')->native(false),
                    Forms\Components\Textarea::make('shipped_notes')
                        ->label('Notas al entregar (para el proponente y votantes)')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Métricas')
                ->collapsed()
                ->schema([
                    Forms\Components\Placeholder::make('votes_breakdown')
                        ->label('Distribución de willingness-to-pay')
                        ->content(fn (?FeatureRequest $record) => $record ? static::votesBreakdown($record) : '—'),
                ]),
        ]);
    }

    protected static function votesBreakdown(FeatureRequest $record): string
    {
        $votes = FeatureVote::where('feature_request_id', $record->id)->get();
        if ($votes->isEmpty()) return 'Sin votos aún.';

        $breakdown = [];
        foreach (FeatureRequest::PRICE_TIERS as $tier => $label) {
            $count = $votes->where('willingness_to_pay', $tier)->count();
            if ($count > 0) $breakdown[] = "{$label}: {$count} voto" . ($count !== 1 ? 's' : '');
        }
        $score = round($record->monetizable_score, 2);
        return implode(' · ', $breakdown) . " | Score monetizable: \${$score}";
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('submittedByUser.name')
                    ->label('Propuesto por')
                    ->description(fn (FeatureRequest $r) => $r->submittedByClinic?->name),
                Tables\Columns\TextColumn::make('votes_count')
                    ->label('Votos')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('monetizable_score')
                    ->label('Score $')
                    ->formatStateUsing(fn ($state) => '$' . number_format((float) $state, 0))
                    ->alignEnd()
                    ->color('success')
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => FeatureRequest::STATUSES[$state] ?? $state)
                    ->colors([
                        'gray' => 'proposed',
                        'info' => 'in_review',
                        'warning' => 'in_progress',
                        'success' => 'shipped',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('votes_count', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(FeatureRequest::STATUSES),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_winner_paid')
                    ->label('Marcar ganadora PAGADA')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn (FeatureRequest $r) => in_array($r->status, ['proposed', 'in_review']))
                    ->requiresConfirmation()
                    ->action(function (FeatureRequest $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'release_type' => 'paid',
                            'winner_month' => now()->format('Y-m'),
                        ]);
                        Notification::make()->title('Marcada como ganadora PAGADA del mes')->success()->send();
                    }),
                Tables\Actions\Action::make('mark_winner_free')
                    ->label('Marcar ganadora GRATIS')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->visible(fn (FeatureRequest $r) => in_array($r->status, ['proposed', 'in_review']))
                    ->requiresConfirmation()
                    ->action(function (FeatureRequest $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'release_type' => 'free',
                            'winner_month' => now()->format('Y-m'),
                        ]);
                        Notification::make()->title('Marcada como ganadora GRATIS del mes')->success()->send();
                    }),
                Tables\Actions\Action::make('ship')
                    ->label('Marcar entregada')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (FeatureRequest $r) => $r->status === 'in_progress')
                    ->form([
                        Forms\Components\Textarea::make('shipped_notes')
                            ->label('Notas de entrega (aparece al proponente + votantes)')
                            ->placeholder('Ya puedes verlo en /doctor/...')
                            ->rows(3),
                    ])
                    ->action(function (FeatureRequest $record, array $data) {
                        $record->update([
                            'status' => 'shipped',
                            'shipped_at' => now(),
                            'shipped_notes' => $data['shipped_notes'] ?? null,
                        ]);
                        Notification::make()->title('Feature marcada como entregada')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (FeatureRequest $r) => in_array($r->status, ['proposed', 'in_review']))
                    ->requiresConfirmation()
                    ->action(fn (FeatureRequest $record) => $record->update(['status' => 'rejected'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatureRequests::route('/'),
            'edit' => Pages\EditFeatureRequest::route('/{record}/edit'),
        ];
    }
}
