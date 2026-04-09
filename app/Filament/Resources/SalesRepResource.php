<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesRepResource\Pages;
use App\Models\Commission;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesRepResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Vendedores';

    protected static ?string $modelLabel = 'Vendedor';

    protected static ?string $pluralModelLabel = 'Vendedores';

    protected static ?string $navigationGroup = 'Ventas';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'sales');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del vendedor')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')->label('Nombre completo')->required(),
                    Forms\Components\TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state)),
                    Forms\Components\TextInput::make('sales_rep_code')
                        ->label('Código vendedor')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Se genera automáticamente'),
                    Forms\Components\TextInput::make('commission_rate_percent')
                        ->label('Comisión %')
                        ->numeric()
                        ->suffix('%')
                        ->helperText('Informativo; el monto viene de 3× mensualidad split 50/50'),
                    Forms\Components\Toggle::make('is_active_sales_rep')
                        ->label('Activo')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->searchable(),
                Tables\Columns\TextColumn::make('sales_rep_code')->label('Código')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('commissions_pending')
                    ->label('Comisiones pendientes')
                    ->state(fn (User $r) => '$' . number_format(
                        Commission::where('user_id', $r->id)->where('status', 'pending')->sum('amount'),
                        2
                    ))
                    ->color('warning'),
                Tables\Columns\TextColumn::make('commissions_paid_total')
                    ->label('Total pagado')
                    ->state(fn (User $r) => '$' . number_format(
                        Commission::where('user_id', $r->id)->where('status', 'paid')->sum('amount'),
                        2
                    ))
                    ->color('success'),
                Tables\Columns\IconColumn::make('is_active_sales_rep')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->label('Alta')->date('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active_sales_rep')->label('Activo'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesReps::route('/'),
            'create' => Pages\CreateSalesRep::route('/create'),
            'edit' => Pages\EditSalesRep::route('/{record}/edit'),
        ];
    }
}
