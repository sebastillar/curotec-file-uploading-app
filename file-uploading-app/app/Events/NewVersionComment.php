<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\FileVersionComment;

class NewVersionComment implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public FileVersionComment $comment)
    {
        \Log::info('NewVersionComment constructor', [
            'comment' => $comment->toArray()
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        \Log::info('Broadcasting NewVersionComment', [
            'channel' => 'file-comments',
            'comment_id' => $this->comment->id,
            'version_id' => $this->comment->file_version_id
        ]);

        return [
            new Channel('file-comments')
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $data = [
            'comment' => [
                'id' => $this->comment->id,
                'comment' => $this->comment->comment,
                'created_at' => $this->comment->created_at,
                'file_version_id' => $this->comment->file_version_id
            ]
        ];

        \Log::info('Broadcasting data:', $data);
        return $data;
    }
}
