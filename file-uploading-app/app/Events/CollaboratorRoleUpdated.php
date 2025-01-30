<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CollaboratorRoleUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $fileId,
        public int $userId,
        public string $role
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('files.' . $this->fileId)
        ];
    }

    public function broadcastAs(): string
    {
        return 'collaborator.role_updated';
    }

    public function broadcastWith(): array
    {
        return [
            'file_id' => $this->fileId,
            'user_id' => $this->userId,
            'role' => $this->role
        ];
    }
}
