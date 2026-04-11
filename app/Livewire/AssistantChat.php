<?php

namespace App\Livewire;

use App\Services\ClinicAssistantAIService;
use Livewire\Component;

class AssistantChat extends Component
{
    public bool $open = false;
    public string $input = '';
    public array $messages = [];
    public bool $thinking = false;

    public bool $aiEnabled = false;

    public function mount(): void
    {
        $this->aiEnabled = \App\Services\AI::enabled();

        $this->messages = [
            [
                'role' => 'assistant',
                'content' => '¡Hola! Soy tu asistente IA. Puedo responder preguntas sobre tu consultorio. Ejemplo: "¿Cuánto facturé esta semana?" o "¿Quién tiene cita mañana?"',
            ],
        ];
    }

    public function toggle(): void
    {
        $this->open = !$this->open;
    }

    public function send(): void
    {
        $q = trim($this->input);
        if (empty($q)) return;

        $this->messages[] = ['role' => 'user', 'content' => $q];
        $this->input = '';
        $this->thinking = true;

        $clinicId = auth()->user()->clinic_id;
        $history = array_slice($this->messages, 0, -1); // exclude the just-added user msg
        $answer = app(ClinicAssistantAIService::class)->ask($clinicId, $q, $history);

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $answer ?: 'No pude procesar tu pregunta. Intenta de nuevo o verifica la configuración de IA.',
        ];
        $this->thinking = false;
    }

    public function clearChat(): void
    {
        $this->messages = [
            [
                'role' => 'assistant',
                'content' => '¡Listo! ¿En qué más te puedo ayudar?',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.assistant-chat');
    }
}
