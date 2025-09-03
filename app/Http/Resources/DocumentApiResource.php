<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'url' => $this->url,
            'file_path' => $this->file_path,
            'project_id' => $this->project_id,
            'notes' => $this->notes,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'extra_information' => $this->extra_information,
            // Include related data if loaded
            'project' => $this->whenLoaded('project', function () {
                return $this->project ? [
                    'id' => $this->project->id,
                    'title' => $this->project->title,
                    'project_url' => $this->project->project_url,
                    'client_id' => $this->project->client_id,
                    'description' => $this->project->description,
                    'status' => $this->project->status,
                    'notes' => $this->project->notes,
                    'created_at' => $this->project->created_at,
                    'updated_at' => $this->project->updated_at,
                ] : null;
            }),
            'updated_by_user' => $this->whenLoaded('updatedBy', function () {
                return $this->updatedBy ? [
                    'id' => $this->updatedBy->id,
                    'name' => $this->updatedBy->name,
                    'email' => $this->updatedBy->email,
                    'username' => $this->updatedBy->username,
                ] : null;
            }),
            // Computed attributes
            'has_file' => !empty($this->file_path),
            'has_url' => !empty($this->url),
        ];
    }
}
