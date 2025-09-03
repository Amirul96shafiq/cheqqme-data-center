<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImportantUrlApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'project_id' => $this->project_id,
            'client_id' => $this->client_id,
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
            'client' => $this->whenLoaded('client', function () {
                return $this->client ? [
                    'id' => $this->client->id,
                    'pic_name' => $this->client->pic_name,
                    'pic_email' => $this->client->pic_email,
                    'company_name' => $this->client->company_name,
                    'company_email' => $this->client->company_email,
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
            'has_project' => !empty($this->project_id),
            'has_client' => !empty($this->client_id),
        ];
    }
}
