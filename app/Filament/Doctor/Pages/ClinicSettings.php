<?php

namespace App\Filament\Doctor\Pages;

use Filament\Forms\Components\FileUpload;
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
                            ->disk('public')
                            ->directory('clinic-logos')
                            ->maxSize(2048)
                            ->helperText('PNG o JPG, máximo 2 MB. Se usa en el portal público y en correos.'),
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
        ]);

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
