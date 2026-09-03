<?php

namespace App\Filament\Doctor\Pages;

use App\Models\Clinic;
use App\Models\ClinicClosure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
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
            'minutos_entre_citas' => $clinic->minutosEntreCitas(),
        ] + $this->horarioParaElFormulario($clinic) + [
            'cierres' => $clinic->closures()
                ->orderBy('starts_on')
                ->get(['starts_on', 'ends_on', 'reason'])
                ->toArray(),
        ]);
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
                                    Toggle::make("horario_{$clave}_cierra_a_comer")
                                        ->label('Cierran a comer')
                                        ->live()
                                        ->visible(fn (callable $get) => $get("horario_{$clave}_abre_ese_dia")),
                                    TimePicker::make("horario_{$clave}_descanso_de")
                                        ->label('Comida de')
                                        ->seconds(false)
                                        ->native(false)
                                        ->visible(fn (callable $get) => $get("horario_{$clave}_abre_ese_dia")
                                            && $get("horario_{$clave}_cierra_a_comer")),
                                    TimePicker::make("horario_{$clave}_descanso_a")
                                        ->label('Comida a')
                                        ->seconds(false)
                                        ->native(false)
                                        ->visible(fn (callable $get) => $get("horario_{$clave}_abre_ese_dia")
                                            && $get("horario_{$clave}_cierra_a_comer")),
                                ]))
                            ->values()
                            ->all()
                    ),

                Section::make('Tiempo entre pacientes')
                    ->description('El rato que necesitas para limpiar el sillón, esterilizar y guardar. Se aparta solo: tu página de agendamiento deja de ofrecer horarios pegados.')
                    ->schema([
                        TextInput::make('minutos_entre_citas')
                            ->label('Minutos entre una cita y la siguiente')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(120)
                            ->step(5)
                            ->suffix('minutos')
                            ->default(0)
                            ->helperText('Déjalo en 0 si no lo necesitas. La mayoría de los consultorios usa entre 10 y 15.'),
                    ]),

                Section::make('Días que cierras')
                    ->description('Vacaciones, días feriados, un congreso. Estos días no aparecen disponibles para tus pacientes aunque sea tu horario normal.')
                    ->schema([
                        Repeater::make('cierres')
                            ->label('')
                            ->addActionLabel('Agregar días cerrados')
                            ->columns(3)
                            ->defaultItems(0)
                            ->schema([
                                DatePicker::make('starts_on')
                                    ->label('Del')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->required(),
                                DatePicker::make('ends_on')
                                    ->label('Al')
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->helperText('El mismo día si es uno solo.')
                                    ->required(),
                                TextInput::make('reason')
                                    ->label('Motivo (opcional)')
                                    ->placeholder('Vacaciones'),
                            ]),
                    ]),

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
            'minutos_entre_citas' => (int) ($data['minutos_entre_citas'] ?? 0),
        ]);

        $this->guardarCierres($clinic, $data['cierres'] ?? []);

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
            $campos["horario_{$dia}_cierra_a_comer"] = ! empty($rango['descanso_de']);
            $campos["horario_{$dia}_descanso_de"] = $rango['descanso_de'] ?? '14:00';
            $campos["horario_{$dia}_descanso_a"] = $rango['descanso_a'] ?? '16:00';
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

            if (! empty($datos["horario_{$dia}_cierra_a_comer"])) {
                $horario[$dia]['descanso_de'] = substr((string) $datos["horario_{$dia}_descanso_de"], 0, 5);
                $horario[$dia]['descanso_a'] = substr((string) $datos["horario_{$dia}_descanso_a"], 0, 5);
            }
        }

        return $horario;
    }

    /**
     * Deja los cierres tal como quedaron en el formulario.
     *
     * Se borran y se vuelven a crear porque son pocos y sin historial que
     * cuidar: es más simple que reconciliar altas, bajas y cambios.
     */
    private function guardarCierres(Clinic $clinic, array $cierres): void
    {
        $clinic->closures()->delete();

        foreach ($cierres as $cierre) {
            if (empty($cierre['starts_on']) || empty($cierre['ends_on'])) {
                continue;
            }

            ClinicClosure::create([
                'clinic_id' => $clinic->id,
                'starts_on' => $cierre['starts_on'],
                'ends_on' => $cierre['ends_on'],
                'reason' => $cierre['reason'] ?? null,
            ]);
        }
    }
}
