<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PremiumServiceResource\Pages;
use App\Models\PremiumService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PremiumServiceResource extends Resource
{
    protected static ?string $model = PremiumService::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Servicios premium';

    protected static ?string $modelLabel = 'Servicio premium';

    protected static ?string $pluralModelLabel = 'Servicios premium';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identidad')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(120),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug (URL)')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->helperText('Solo minúsculas, guiones. Ej: setup-migracion.'),
                    Forms\Components\Select::make('category')
                        ->label('Categoría')
                        ->options(PremiumService::CATEGORIES)
                        ->required(),
                    Forms\Components\Select::make('target_audience')
                        ->label('Audiencia')
                        ->options(PremiumService::TARGET_AUDIENCE)
                        ->required()
                        ->default('all'),
                ])->columns(2),

            Forms\Components\Section::make('Precio y SLA')
                ->schema([
                    Forms\Components\TextInput::make('price_mxn')
                        ->label('Precio MXN')
                        ->required()
                        ->numeric()
                        ->prefix('$'),
                    Forms\Components\Select::make('pricing_type')
                        ->label('Tipo de pago')
                        ->options(PremiumService::PRICING_TYPES)
                        ->required()
                        ->default('one_time'),
                    Forms\Components\TextInput::make('sla_days')
                        ->label('SLA (días hábiles de entrega)')
                        ->required()
                        ->numeric()
                        ->default(3)
                        ->minValue(1),
                    Forms\Components\TextInput::make('seller_commission_pct')
                        ->label('% comisión al vendedor')
                        ->required()
                        ->numeric()
                        ->default(20)
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%'),
                ])->columns(2),

            Forms\Components\Section::make('Contenido de marketing')
                ->schema([
                    Forms\Components\TextInput::make('short_desc')
                        ->label('Descripción corta (subtítulo)')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\Textarea::make('long_desc')
                        ->label('Descripción larga')
                        ->rows(6)
                        ->helperText('Markdown admitido (saltos de línea, listas).'),
                    Forms\Components\Repeater::make('bullets')
                        ->label('Puntos destacados (bullets)')
                        ->schema([Forms\Components\TextInput::make('')->required()])
                        ->defaultItems(3)
                        ->simple(Forms\Components\TextInput::make('value')->required()),
                ]),

            Forms\Components\Section::make('Intake post-compra')
                ->schema([
                    Forms\Components\Toggle::make('requires_intake')
                        ->label('Requiere formulario después de pagar')
                        ->reactive(),
                    Forms\Components\Textarea::make('intake_form_schema')
                        ->label('Schema JSON del intake')
                        ->rows(8)
                        ->visible(fn (Forms\Get $get) => $get('requires_intake'))
                        ->helperText('Array JSON con {field, label, type, required}.'),
                ]),

            Forms\Components\Section::make('Visibilidad')
                ->schema([
                    Forms\Components\Toggle::make('is_active')->label('Activo (visible en marketplace)')->default(true),
                    Forms\Components\Toggle::make('is_featured')->label('Destacado (arriba en marketplace)'),
                    Forms\Components\TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Servicio')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->formatStateUsing(fn ($state) => PremiumService::CATEGORIES[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('price_mxn')->label('Precio')->money('MXN')->sortable(),
                Tables\Columns\TextColumn::make('pricing_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => PremiumService::PRICING_TYPES[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => $state === 'monthly' ? 'success' : ($state === 'custom_quote' ? 'warning' : 'primary')),
                Tables\Columns\IconColumn::make('is_featured')->label('Destacado')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('purchases_count')
                    ->label('Compras')
                    ->counts('purchases')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options(PremiumService::CATEGORIES),
                Tables\Filters\TernaryFilter::make('is_active')->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPremiumServices::route('/'),
            'create' => Pages\CreatePremiumService::route('/create'),
            'edit' => Pages\EditPremiumService::route('/{record}/edit'),
        ];
    }
}
