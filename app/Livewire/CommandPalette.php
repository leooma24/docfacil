<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Patient;
use App\Services\ClinicAssistantAIService;
use Livewire\Component;

class CommandPalette extends Component
{
    public bool $open = false;
    public string $query = '';
    public array $results = [];
    public int $selectedIndex = 0;
    public bool $searching = false;
    public ?string $aiAnswer = null;
    public bool $askingAi = false;

    protected $listeners = ['openCommandPalette' => 'openPalette'];

    public function openPalette(): void
    {
        $this->open = true;
        $this->query = '';
        $this->results = [];
        $this->aiAnswer = null;
        $this->selectedIndex = 0;
    }

    public function close(): void
    {
        $this->open = false;
        $this->query = '';
        $this->results = [];
        $this->aiAnswer = null;
    }

    public function updatedQuery(): void
    {
        $this->aiAnswer = null;
        $this->selectedIndex = 0;

        $q = trim($this->query);
        if (strlen($q) < 2) {
            $this->results = $this->getDefaultActions();
            return;
        }

        $clinicId = auth()->user()->clinic_id;
        $results = [];

        // Patients
        $patients = Patient::where('clinic_id', $clinicId)
            ->where(function ($qb) use ($q) {
                $qb->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get();

        foreach ($patients as $p) {
            $results[] = [
                'type' => 'patient',
                'icon' => '👤',
                'title' => "{$p->first_name} {$p->last_name}",
                'subtitle' => $p->phone ?: 'Paciente',
                'url' => route('filament.doctor.pages.perfil-paciente', ['patient' => $p->id]),
            ];
        }

        // Today's appointments
        $appts = Appointment::where('clinic_id', $clinicId)
            ->whereDate('starts_at', today())
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->with('patient')
            ->get()
            ->filter(function ($a) use ($q) {
                $name = strtolower(($a->patient->first_name ?? '') . ' ' . ($a->patient->last_name ?? ''));
                return str_contains($name, strtolower($q));
            })
            ->take(3);

        foreach ($appts as $a) {
            $results[] = [
                'type' => 'appointment',
                'icon' => '📅',
                'title' => "Cita de {$a->patient->first_name} {$a->patient->last_name}",
                'subtitle' => 'Hoy ' . $a->starts_at->format('H:i') . ' — Click para iniciar consulta',
                'url' => route('filament.doctor.pages.consulta', ['appointment' => $a->id]),
            ];
        }

        // Quick actions matching query
        $actions = $this->getActionsForQuery($q);
        $results = array_merge($results, $actions);

        $this->results = $results;
    }

    protected function getDefaultActions(): array
    {
        return [
            ['type' => 'action', 'icon' => '🏠', 'title' => 'Ir al Escritorio', 'subtitle' => 'Dashboard', 'url' => '/doctor'],
            ['type' => 'action', 'icon' => '📅', 'title' => 'Calendario', 'subtitle' => 'Ver agenda', 'url' => '/doctor/calendario'],
            ['type' => 'action', 'icon' => '👤', 'title' => 'Nuevo paciente', 'subtitle' => 'Crear paciente', 'url' => '/doctor/pacientes/create'],
            ['type' => 'action', 'icon' => '➕', 'title' => 'Nueva cita', 'subtitle' => 'Agendar cita', 'url' => '/doctor/citas/create'],
            ['type' => 'action', 'icon' => '💊', 'title' => 'Nueva receta', 'subtitle' => 'Crear receta', 'url' => '/doctor/recetas/create'],
            ['type' => 'action', 'icon' => '💰', 'title' => 'Registrar cobro', 'subtitle' => 'Nuevo pago', 'url' => '/doctor/cobros/create'],
            ['type' => 'action', 'icon' => '🩺', 'title' => 'Consulta rápida', 'subtitle' => 'Iniciar walk-in', 'url' => '/doctor/consulta'],
            ['type' => 'action', 'icon' => '📱', 'title' => 'Check-in QR', 'subtitle' => 'Código para recepción', 'url' => '/doctor/check-in-qr'],
        ];
    }

    protected function getActionsForQuery(string $q): array
    {
        $q = strtolower($q);
        $all = $this->getDefaultActions();

        return array_values(array_filter($all, function ($a) use ($q) {
            return str_contains(strtolower($a['title']), $q)
                || str_contains(strtolower($a['subtitle']), $q);
        }));
    }

    public function askAi(): void
    {
        if (empty(trim($this->query))) return;

        $this->askingAi = true;
        $clinicId = auth()->user()->clinic_id;
        $this->aiAnswer = app(ClinicAssistantAIService::class)->ask($clinicId, $this->query);
        $this->askingAi = false;
    }

    public function selectResult(int $index): void
    {
        if (!isset($this->results[$index])) return;
        $url = $this->results[$index]['url'] ?? null;
        if ($url) {
            $this->redirect($url, navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.command-palette');
    }
}
