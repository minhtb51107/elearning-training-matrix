<?php

namespace App\Events;

use App\Models\TrainingRequest;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrainingRequestSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $trainingRequest;
    public $requester;

    public function __construct(TrainingRequest $trainingRequest, User $requester)
    {
        $this->trainingRequest = $trainingRequest;
        $this->requester = $requester;
    }
}