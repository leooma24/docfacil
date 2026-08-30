<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Clinic;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Configuración del consultorio: logo, URLs publicas para integraciones
 * (Google reseñas, redes sociales). Editable en cualquier momento.
 */
class ClinicSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configuración';

    protected static ?string $title = 'Configuración del consultorio';

    protected static string $view = 'filament.doctor.pages.clinic-settings';

    protected static ?int $navigationSort = 98;

    protected static ?string $navigationGroup = 'Mi cuenta';

    public ?array $data = [];

    public function mount(): void
    {
        $clinic = auth()->user()->clinic;
        $this->form->fill([
            'name' => $clinic->name,
            'phone' => $clinic->phone,
            'address' => $clinic->address,
            'city' => $clinic->city,
            'logo' => $clinic->logo,
            'google_review_url' => $clinic->google_review_url,
        ] + $this->horarioParaElFormulario($clinic));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Datos básicos')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('Nombre del consultorio')->required()->maxLength(255),
                        TextInput::make('phone')->label('Teléfono')->tel()->maxLength(20),
                        TextInput::make('address')->label('Dirección')->maxLength(255)->columnSpanFull(),
                        TextInput::make('city')->label('Ciudad')->maxLength(100),
                        FileUpload::make('logo')
                            ->label('Logo del consultorio')
                            ->image()
                            ->imageEditor()
                            // Solo PNG/JPG/WebP — SVG explícitamente excluido por riesgo de XSS embebido
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->disk('public')
                            ->directory('clinic-logos')
                            ->maxSize(2048)
                            ->helperText('PNG, JPG o WebP, máximo 2 MB. Se usa en el portal público y en correos.'),
                    ]),
                Section::make('Horario de atención')
                    ->description('Con esto, tus pacientes no pueden pedir cita cuando estás cerrado desde tu página de agendamiento.')
                    ->schema(
                        collect(Clinic::DIAS)
                            ->map(fn (string $nombre, string $clave) => Fieldset::make($nombre)
                                ->columns(3)
                                ->schema([
                                    Toggle::make("horario_{$clave}_abre_ese_dia")
                                        ->label('Abre')
                                        ->live(),
                                    TimePicker::make("horario_{$clave}_abre")
                                        ->label('De')
                                        ->seconds(false)
                                        ->native(false)
                                        ->visible(fn (callable $get) => $get("horario_{$clave}_abre_ese_dia")),
                                    TimePicker::make("horario_{$clave}_cierra")
                                        ->label('A')
                                        ->seconds(false)
                                        ->native(false)
                                        ->visible(fn (callable $get) => $get("horario_{$clave}_abre_ese_dia")),
                                ]))
                            ->values()
                            ->all()
                    ),

                Section::make('Integraciones')
                    ->description('URLs públicas de tu consultorio para que DocFácil te ayude a aprovecharlas.')
                    ->schema([
                        TextInput::make('google_review_url')
                            ->label('Link de tu reseña en Google')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://g.page/r/...')
                            ->helperText(new \Illuminate\Support\HtmlString(
                                'Abre tu <a href="https://business.google.com" target="_blank" class="text-teal-600 underline">perfil de Google Business</a>, '.
                                'haz clic en "Reseñas" > "Recibir más reseñas" y copia el link corto. '.
                                'Se usa en el add-on de <strong>Reseñas Google automatizadas</strong>.'
                            )),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $clinic = auth()->user()->clinic;

        $clinic->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'logo' => $data['logo'] ?? null,
            'google_review_url' => $data['google_review_url'] ?? null,
            'working_hours' => $this->horarioDesdeElFormulario($data),
        ]);

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }

    /**
     * El horario guardado, aplanado a los campos del formulario.
     *
     * Se guarda como un JSON con una entrada por dia, pero el formulario
     * necesita un campo por dato, asi que traducimos en los dos sentidos.
     */
    private function horarioParaElFormulario(Clinic $clinic): array
    {
        $campos = [];

        foreach ($clinic->horario() as $dia => $rango) {
            $campos["horario_{$dia}_abre_ese_dia"] = $rango !== null;
            $campos["horario_{$dia}_abre"] = $rango['abre'] ?? '09:00';
            $campos["horario_{$dia}_cierra"] = $rango['cierra'] ?? '19:00';
        }

        return $campos;
    }

    /** Los campos del formulario, de vuelta al JSON que guarda el modelo. */
    private function horarioDesdeElFormulario(array $datos): array
    {
        $horario = [];

        foreach (array_keys(Clinic::DIAS) as $dia) {
            if (empty($datos["horario_{$dia}_abre_ese_dia"])) {
                $horario[$dia] = null;   // cerrado
                continue;
            }

            $horario[$dia] = [
                'abre' => substr((string) $datos["horario_{$dia}_abre"], 0, 5),
                'cierra' => substr((string) $datos["horario_{$dia}_cierra"], 0, 5),
            ];
        }

        return $horario;
    }
}
