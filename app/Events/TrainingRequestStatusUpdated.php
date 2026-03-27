<?php

namespace App\Events;

use App\Models\TrainingRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrainingRequestStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trainingRequest;
    public $status;

    public function __construct(TrainingRequest $trainingRequest, string $status)
    {
        $this->trainingRequest = $trainingRequest;
        $this->status = $status;
    }
}