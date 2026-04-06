<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\ConsentFormResource\Pages;
use App\Models\ConsentForm;
use App\Models\Doctor;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConsentFormResource extends Resource
{
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', auth()->user()->clinic_id);
    }

    protected static ?string $model = ConsentForm::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Consentimientos';

    protected static ?string $modelLabel = 'Consentimiento';

    protected static ?string $pluralModelLabel = 'Consentimientos';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationGroup = 'Dental';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos del Consentimiento')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->label('Paciente')
                            ->relationship('patient')
                            ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable(['first_name', 'last_name'])
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('doctor_id')
                            ->label('Doctor')
                            ->relationship('doctor')
                            ->getOptionLabelFromRecordUsing(fn (Doctor $record) => $record->user?->name ?? '')
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Título del documento')
                            ->required()
                            ->default('Consentimiento Informado')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('procedure_name')
                            ->label('Procedimiento')
                            ->placeholder('Ej: Extracción de tercer molar')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Contenido del Documento')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Texto del consentimiento')
                            ->required()
                            ->default(self::getDefaultContent())
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('risks')
                            ->label('Riesgos del procedimiento')
                            ->rows(3)
                            ->placeholder('Describa los riesgos asociados al procedimiento...'),
                        Forms\Components\Textarea::make('alternatives')
                            ->label('Alternativas al tratamiento')
                            ->rows(3)
                            ->placeholder('Describa las alternativas disponibles...'),
                    ]),
                Forms\Components\Section::make('Firma Digital')
                    ->schema([
                        Forms\Components\FileUpload::make('signature')
                            ->label('Firma del paciente')
                            ->image()
                            ->directory('signatures')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('El paciente puede firmar en la tablet/teléfono y subir la imagen.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($record) => "{$record->patient->first_name} {$record->patient->last_name}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Documento')
                    ->limit(30),
                Tables\Columns\TextColumn::make('procedure_name')
                    ->label('Procedimiento')
                    ->placeholder('Sin especificar'),
                Tables\Columns\IconColumn::make('signed_at')
                    ->label('Firmado')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->signed_at !== null),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_signed')
                    ->label('Marcar firmado')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('¿El paciente ha firmado este consentimiento?')
                    ->visible(fn (ConsentForm $record) => !$record->isSigned())
                    ->action(fn (ConsentForm $record) => $record->update([
                        'signed_at' => now(),
                        'signed_ip' => request()->ip(),
                    ])),
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->action(function (ConsentForm $record) {
                        $record->load(['patient', 'doctor.user', 'doctor.clinic']);

                        $pdf = Pdf::loadView('pdf.consent-form', [
                            'consent' => $record,
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "consentimiento-{$record->id}-{$record->patient->last_name}.pdf"
                        );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsentForms::route('/'),
            'create' => Pages\CreateConsentForm::route('/create'),
            'edit' => Pages\EditConsentForm::route('/{record}/edit'),
        ];
    }

    private static function getDefaultContent(): string
    {
        return '<p>Yo, el/la paciente abajo firmante, declaro que he sido informado(a) de manera clara y comprensible por el/la Dr(a). sobre el procedimiento a realizarse.</p>'
            . '<p>Se me ha explicado:</p>'
            . '<ul><li>La naturaleza del procedimiento y en qué consiste</li>'
            . '<li>Los beneficios esperados del tratamiento</li>'
            . '<li>Los riesgos y posibles complicaciones</li>'
            . '<li>Las alternativas de tratamiento disponibles</li>'
            . '<li>Las consecuencias de no realizar el tratamiento</li></ul>'
            . '<p>He tenido la oportunidad de hacer preguntas y todas han sido respondidas satisfactoriamente.</p>'
            . '<p>Por lo tanto, de manera libre y voluntaria, autorizo la realización del procedimiento descrito.</p>';
    }
}
