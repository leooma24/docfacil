<?php

namespace App\Filament\Doctor\Pages;

use App\Models\ClinicConsultationSettings;
use App\Models\DoctorConsultationSettings;
use App\Services\SpecialtyService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/**
 * Página unificada de configuración de campos de la pantalla de Consulta.
 *
 * Dos niveles que conviven con cascada:
 *  - Clínica (afecta a todos los doctores que heredan)
 *  - Personal (override del doctor — sobrescribe lo de la clínica)
 *
 * Cascada al resolver: doctor con override → clínica → defaults por especialidad.
 * Implementado en SpecialtyService::resolveEnabledFields().
 */
class ConsultationFieldsSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Campos de consulta';

    protected static ?string $title = 'Configuración de campos de la consulta';

    protected static ?string $slug = 'configuracion/campos-consulta';

    protected static string $view = 'filament.doctor.pages.consultation-fields-settings';

    protected static ?int $navigationSort = 97;

    protected static ?string $navigationGroup = 'Mi cuenta';

    // Pestaña activa: 'clinic' o 'mine'
    #[Url]
    public string $tab = 'clinic';

    /**
     * @var array<string, bool>  ['blood_pressure' => true, 'temperature' => false, ...]
     * Estado actual del formulario para los campos de la pestaña activa.
     */
    public array $enabled = [];

    public bool $inheritsClinicConfig = true;

    public function mount(): void
    {
        $this->loadCurrentTab();
    }

    public function updatedTab(): void
    {
        $this->loadCurrentTab();
    }

    public function updatedInheritsClinicConfig(): void
    {
        // Si el doctor activa la herencia, cargamos los campos de la clínica
        // como referencia visual (pero quedan disabled en la UI).
        if ($this->tab === 'mine' && $this->inheritsClinicConfig) {
            $clinicEnabled = $this->getClinicEnabledFields();
            $this->enabled = $this->toToggleMap($clinicEnabled);
        }
    }

    /**
     * Carga el estado inicial del formulario según la pestaña activa.
     */
    protected function loadCurrentTab(): void
    {
        if ($this->tab === 'clinic') {
            $current = $this->getClinicEnabledFields();
            $this->enabled = $this->toToggleMap($current);
            return;
        }

        // tab === 'mine'
        $doctor = auth()->user()->doctor;
        $settings = $doctor?->consultationSettings;
        $this->inheritsClinicConfig = $settings ? (bool) $settings->inherits_clinic_config : true;

        if ($this->inheritsClinicConfig) {
            // Cuando hereda: mostramos los campos de la clínica como referencia
            $clinicEnabled = $this->getClinicEnabledFields();
            $this->enabled = $this->toToggleMap($clinicEnabled);
            return;
        }

        // Tiene override: mostramos sus campos
        $current = is_array($settings?->enabled_fields) ? $settings->enabled_fields : $this->getClinicEnabledFields();
        $this->enabled = $this->toToggleMap($current);
    }

    /**
     * Convierte ['blood_pressure', 'heart_rate'] → ['blood_pressure' => true, 'heart_rate' => true, 'temperature' => false, ...]
     */
    protected function toToggleMap(array $enabledList): array
    {
        $map = [];
        foreach (array_keys(SpecialtyService::FIELD_CATALOG) as $key) {
            $map[$key] = in_array($key, $enabledList, true);
        }
        return $map;
    }

    protected function getClinicEnabledFields(): array
    {
        $clinic = auth()->user()->clinic;
        $settings = $clinic?->consultationSettings;
        if ($settings && is_array($settings->enabled_fields)) {
            return $settings->enabled_fields;
        }
        // Si la clínica no tiene config, usar defaults por especialidad del doctor actual
        return SpecialtyService::defaultConsultationFields(auth()->user()->doctor?->specialty);
    }

    public function save(): void
    {
        $selectedKeys = array_keys(array_filter($this->enabled));

        if ($this->tab === 'clinic') {
            $clinic = auth()->user()->clinic;
            ClinicConsultationSettings::updateOrCreate(
                ['clinic_id' => $clinic->id],
                ['enabled_fields' => array_values($selectedKeys)]
            );
            Notification::make()
                ->title('Configuración guardada')
                ->body('Los doctores que heredan ahora verán estos campos.')
                ->success()
                ->send();
            return;
        }

        // tab === 'mine'
        $doctor = auth()->user()->doctor;
        if (! $doctor) {
            Notification::make()->title('Tu usuario no tiene perfil de doctor')->danger()->send();
            return;
        }

        DoctorConsultationSettings::updateOrCreate(
            ['doctor_id' => $doctor->id],
            [
                'enabled_fields' => $this->inheritsClinicConfig ? null : array_values($selectedKeys),
                'inherits_clinic_config' => $this->inheritsClinicConfig,
            ]
        );

        Notification::make()
            ->title('Tu configuración personal se guardó')
            ->body($this->inheritsClinicConfig
                ? 'Heredas la configuración de tu clínica.'
                : 'Tus campos prevalecen sobre los de la clínica.')
            ->success()
            ->send();
    }

    public function resetToDefaults(): void
    {
        $specialty = auth()->user()->doctor?->specialty;
        $defaults = SpecialtyService::defaultConsultationFields($specialty);
        $this->enabled = $this->toToggleMap($defaults);

        Notification::make()
            ->title('Restaurado a defaults de tu especialidad')
            ->body('Los campos por defecto se cargaron. No olvides guardar.')
            ->success()
            ->send();
    }

    public function getFieldsByGroupProperty(): array
    {
        $grouped = [];
        foreach (SpecialtyService::FIELD_CATALOG as $key => $meta) {
            $grouped[$meta['group']][] = ['key' => $key, 'label' => $meta['label'], 'help' => $meta['help']];
        }
        return $grouped;
    }

    public function getGroupLabelsProperty(): array
    {
        return [
            'vitals' => 'Signos vitales',
            'somatometry' => 'Somatometría',
            'diagnosis' => 'Diagnóstico',
            'extras' => 'Banners / Alertas',
        ];
    }

    public function getSpecialtyLabelProperty(): string
    {
        return SpecialtyService::getSpecialtyLabel(auth()->user()->doctor?->specialty);
    }
}
