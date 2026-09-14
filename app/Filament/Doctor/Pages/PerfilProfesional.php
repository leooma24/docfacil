<?php

namespace App\Filament\Doctor\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Los datos del doctor que van en sus recetas y en su página pública.
 *
 * La cédula solo se capturaba una vez, en el onboarding, y después no había
 * dónde corregirla. A la receta además le faltaban la institución que
 * expidió el título y la cédula de especialidad (reglamento de atención
 * médica, arts. 64 y 65; la Ley General de Salud, art. 83, también las pide
 * en su publicidad).
 */
class PerfilProfesional extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Mi perfil profesional';

    protected static ?string $title = 'Mi perfil profesional';

    protected static ?string $slug = 'perfil-profesional';

    protected static ?string $navigationGroup = 'Mi cuenta';

    protected static ?int $navigationSort = 97;

    protected static string $view = 'filament.doctor.pages.perfil-profesional';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->doctor !== null;
    }

    public function mount(): void
    {
        $doctor = auth()->user()->doctor;

        $this->form->fill([
            'license_number' => $doctor->license_number,
            'institucion_titulo' => $doctor->institucion_titulo,
            'specialty' => $doctor->specialty,
            'cedula_especialidad' => $doctor->cedula_especialidad,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Lo que va en tus recetas')
                    ->description('La ley pide que tus recetas y tu página pública digan tu cédula y la institución que te dio el título. Lo capturas una vez y sale en todas.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('license_number')
                            ->label('Cédula profesional')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('institucion_titulo')
                            ->label('Institución que expidió tu título')
                            ->placeholder('Universidad Autónoma de Sinaloa')
                            ->required()
                            ->maxLength(150),
                        TextInput::make('specialty')
                            ->label('Especialidad')
                            ->placeholder('Odontología general, Ortodoncia…')
                            ->maxLength(255),
                        TextInput::make('cedula_especialidad')
                            ->label('Cédula de especialidad')
                            ->helperText('Solo si eres especialista.')
                            ->maxLength(50),
                    ]),
            ]);
    }

    public function guardar(): void
    {
        auth()->user()->doctor->update($this->form->getState());

        Notification::make()
            ->title('Perfil guardado')
            ->body('Tus recetas ya salen con estos datos.')
            ->success()
            ->send();
    }

    /** Lo que le faltaba a la receta, cuando lo mandaron aquí desde el PDF. */
    public function getFaltabaParaReceta(): array
    {
        return (array) session('falta_para_receta', []);
    }
}
