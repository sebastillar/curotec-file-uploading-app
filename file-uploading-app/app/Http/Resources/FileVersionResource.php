<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileVersionResource extends JsonResource
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
            'version_number' => $this->version_number,
            'path' => $this->path,
            'download_url' => route('files.versions.download', $this->id),
            'comments' => FileVersionCommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->whenCounted('comments'),
            'file' => new FileResource($this->whenLoaded('file')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
