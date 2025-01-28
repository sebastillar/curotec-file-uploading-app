<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileVersionCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'version' => new FileVersionResource($this->whenLoaded('fileVersion')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
