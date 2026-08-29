<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Editor del blog público.
 *
 * El cuerpo del artículo se arma con bloques tipados (párrafo, subtítulo,
 * lista, tabla, cita de acción) en vez de un editor de HTML libre. Así el
 * artículo siempre sale con el diseño del sitio, y quien escribe no puede
 * romper el maquetado sin darse cuenta.
 */
class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $slug = 'blog';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'Blog';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $modelLabel = 'Artículo';

    protected static ?string $pluralModelLabel = 'Artículos del blog';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('El artículo')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->placeholder('Cuánto cuesta abrir un consultorio dental en México')
                        ->helperText('Es lo que se ve en Google. Entre 50 y 65 caracteres funciona mejor.')
                        ->required()
                        ->maxLength(160)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Forms\Set $set, string $operation) {
                            // Solo al crear: cambiarle el slug a un artículo ya
                            // publicado le tira las posiciones que haya ganado.
                            if ($operation === 'create' && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->label('Dirección (slug)')
                        ->prefix('/blog/')
                        ->helperText('No lo cambies una vez publicado: pierdes lo que hayas ganado en Google.')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(160),

                    Forms\Components\Textarea::make('description')
                        ->label('Bajada / meta description')
                        ->helperText('El texto que aparece debajo del título en Google. Alrededor de 155 caracteres.')
                        ->rows(2)
                        ->required()
                        ->maxLength(300),

                    Forms\Components\Select::make('category')
                        ->label('Categoría')
                        ->options(BlogPost::CATEGORIAS)
                        ->default('Gestión')
                        ->required(),

                    Forms\Components\FileUpload::make('cover_image')
                        ->label('Imagen de portada')
                        ->helperText('Es la miniatura al compartir en WhatsApp o Facebook. Sin ella el enlace se ve desnudo. Ideal 1200×630.')
                        ->image()
                        ->directory('blog')
                        ->disk('public'),
                        // Sin imageEditor(): su recortador tiraba
                        // "getCroppedCanvas is not a function" y ademas metia
                        // un segundo boton "Guardar" en la pantalla, que
                        // confunde con el del articulo.
                ])
                ->columns(2),

            Forms\Components\Section::make('Cuerpo')
                ->description('Se arma por bloques para que siempre salga con el diseño del sitio.')
                ->schema([
                    // Filament guarda cada bloque como ['type' => x, 'data' => [...]],
                    // pero la vista del blog los lee planos: ['type' => x, 'text' => ...].
                    // Traducimos aqui, en la frontera, para no tocar ni la vista
                    // ni lo que ya esta guardado.
                    Forms\Components\Builder::make('content')
                        ->label('')
                        ->required()
                        ->collapsible()
                        ->cloneable()
                        ->blockNumbers(false)
                        ->afterStateHydrated(function (Forms\Components\Builder $component, $state) {
                            $component->state(collect($state ?? [])->map(function (array $bloque) {
                                if (array_key_exists('data', $bloque)) {
                                    return $bloque;   // ya viene en forma de Filament
                                }

                                $tipo = $bloque['type'] ?? 'p';
                                unset($bloque['type']);

                                // La tabla se edita como texto: primer renglon
                                // encabezados, columnas separadas por |.
                                if ($tipo === 'ul') {
                                    $bloque = ['lista' => implode("
", $bloque['items'] ?? [])];
                                }

                                if ($tipo === 'table') {
                                    $renglones = array_merge(
                                        [$bloque['head'] ?? []],
                                        $bloque['rows'] ?? []
                                    );
                                    $bloque = [
                                        'tabla' => collect($renglones)
                                            ->map(fn ($fila) => implode(' | ', (array) $fila))
                                            ->implode("
"),
                                        'caption' => $bloque['caption'] ?? null,
                                    ];
                                }

                                return ['type' => $tipo, 'data' => $bloque];
                            })->values()->all());
                        })
                        // Al guardar, BlogPost::setContentAttribute() los
                        // regresa a la forma plana que lee la vista.
                        ->blocks([
                            Forms\Components\Builder\Block::make('p')
                                ->label('Párrafo')
                                ->icon('heroicon-o-bars-3-bottom-left')
                                ->schema([
                                    Forms\Components\Textarea::make('text')
                                        ->label('')
                                        ->rows(4)
                                        ->required(),
                                ]),

                            Forms\Components\Builder\Block::make('h2')
                                ->label('Subtítulo')
                                ->icon('heroicon-o-h1')
                                ->schema([
                                    Forms\Components\TextInput::make('text')->label('')->required(),
                                ]),

                            Forms\Components\Builder\Block::make('h3')
                                ->label('Sub-subtítulo')
                                ->icon('heroicon-o-h2')
                                ->schema([
                                    Forms\Components\TextInput::make('text')->label('')->required(),
                                ]),

                            Forms\Components\Builder\Block::make('ul')
                                ->label('Lista de viñetas')
                                ->icon('heroicon-o-list-bullet')
                                ->schema([
                                    // Un renglon por viñeta, igual que la tabla:
                                    // se escribe de corrido y se puede pegar
                                    // desde cualquier lado.
                                    Forms\Components\Textarea::make('lista')
                                        ->label('')
                                        ->rows(5)
                                        ->required()
                                        ->helperText('Una viñeta por renglon.')
                                        ->placeholder("Titulo y cedula profesional vigentes.
Alta en el SAT y RFC del consultorio.
Uso de suelo compatible con servicios de salud."),
                                ]),

                            Forms\Components\Builder\Block::make('table')
                                ->label('Tabla')
                                ->icon('heroicon-o-table-cells')
                                ->schema([
                                    // Se escribe como texto, un renglon por fila
                                    // y columnas separadas por |. Es mucho mas
                                    // rapido que ir agregando celda por celda,
                                    // y se puede pegar desde una hoja de calculo.
                                    Forms\Components\Textarea::make('tabla')
                                        ->label('')
                                        ->rows(6)
                                        ->required()
                                        ->helperText('Un renglon por fila y las columnas separadas por |. El primer renglon son los encabezados.')
                                        ->placeholder("Nivel | Inversion | Que incluye
Basico | \$250,000 | Unidad dental, compresor
Intermedio | \$700,000 | Lo anterior + radiovisiografo"),
                                    Forms\Components\TextInput::make('caption')
                                        ->label('Nota al pie (opcional)')
                                        ->placeholder('Precios de referencia 2026 en pesos mexicanos.'),
                                ]),

                            Forms\Components\Builder\Block::make('cta')
                                ->label('Llamado a la acción')
                                ->icon('heroicon-o-megaphone')
                                ->schema([
                                    Forms\Components\Textarea::make('text')
                                        ->label('')
                                        ->rows(2)
                                        ->helperText('Va en la caja verde del final, con el botón de registro.')
                                        ->required(),
                                ]),
                        ]),
                ]),

            Forms\Components\Section::make('Publicación')
                ->schema([
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Fecha de publicación')
                        ->helperText('Vacío = borrador, no se ve en el sitio. Una fecha futura lo programa.')
                        ->native(false)
                        ->displayFormat('d/m/Y H:i')
                        ->seconds(false),

                    Forms\Components\TextInput::make('read_time')
                        ->label('Tiempo de lectura')
                        ->placeholder('Se calcula solo')
                        ->helperText('Déjalo vacío y lo calculamos por el largo del texto.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Portada')
                    ->disk('public')
                    ->height(40),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->wrap()
                    ->limit(70),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (BlogPost $record) => match (true) {
                        $record->published_at === null => 'gray',
                        $record->published_at->isFuture() => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($state, BlogPost $record) => match (true) {
                        $record->published_at === null => 'Borrador',
                        $record->published_at->isFuture() => 'Programado ' . $record->published_at->format('d/m/Y'),
                        default => 'Publicado ' . $record->published_at->format('d/m/Y'),
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('read_time')
                    ->label('Lectura')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options(BlogPost::CATEGORIAS),
                Tables\Filters\TernaryFilter::make('published_at')
                    ->label('Publicados')
                    ->nullable()
                    ->placeholder('Todos')
                    ->trueLabel('Solo publicados')
                    ->falseLabel('Solo borradores'),
            ])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver en el sitio')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (BlogPost $record) => url("/blog/{$record->slug}"))
                    ->openUrlInNewTab()
                    ->visible(fn (BlogPost $record) => $record->estaPublicado()),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Todavía no hay artículos')
            ->emptyStateDescription('El blog es lo que trae visitas desde Google. Un artículo bien hecho sigue trayendo gente meses después de publicarlo.')
            ->emptyStateIcon('heroicon-o-newspaper')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
