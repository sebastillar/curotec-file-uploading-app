<?php

namespace App\Events;

use App\Models\FileCollaborator;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CollaboratorAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public FileCollaborator $collaborator
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('files.' . $this->collaborator->file_id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'collaborator.added';
    }

    public function broadcastWith(): array
    {
        return [
            'collaborator' => [
                'id' => $this->collaborator->id,
                'user' => $this->collaborator->user->only(['id', 'name', 'email']),
                'role' => $this->collaborator->role,
                'created_at' => $this->collaborator->created_at
            ]
        ];
    }
}
