<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientCheckedIn implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * T12: حدث "المريض وصل ويانتظر"
     */
    public $appointment;
    public $patientName;
    public $treatmentType;
    public $queuePosition;
    public $chronicConditions;

    public function __construct($appointment, $queuePosition)
    {
        $this->appointment = $appointment;
        $this->patientName = $appointment->patient->name;
        $this->treatmentType = $appointment->treatment_type ?? 'عام';
        $this->chronicConditions = $appointment->patient->chronic_conditions ?? [];
        $this->queuePosition = $queuePosition;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('doctor.' . $this->appointment->doctor_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'patient_name' => $this->patientName,
            'treatment_type' => $this->treatmentType,
            'chronic_conditions' => $this->chronicConditions,
            'queue_position' => $this->queuePosition,
            'checked_in_at' => now()->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'patient.checked-in';
    }
}
