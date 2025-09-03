<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientApiResource extends JsonResource
{
 public function toArray($request)
 {
  return [
   'id' => $this->id,
   'pic_name' => $this->pic_name,
   'pic_email' => $this->pic_email,
   'pic_contact_number' => $this->pic_contact_number,
   'company_name' => $this->company_name,
   'company_email' => $this->company_email,
   'company_address' => $this->company_address,
   'billing_address' => $this->billing_address,
   'notes' => $this->notes,
   'created_at' => $this->created_at,
   'updated_at' => $this->updated_at,
   'deleted_at' => $this->deleted_at,
   'updated_by' => $this->updated_by,
   'extra_information' => $this->extra_information,
   // Include related data if loaded
   'projects' => $this->whenLoaded('projects', function () {
    return $this->projects->map(function ($project) {
     return [
      'id' => $project->id,
      'title' => $project->title,
      'project_url' => $project->project_url,
      'description' => $project->description,
      'status' => $project->status,
      'notes' => $project->notes,
      'created_at' => $project->created_at,
      'updated_at' => $project->updated_at,
      'updated_by' => $project->updated_by,
      'extra_information' => $project->extra_information,
     ];
    });
   }),
   'documents' => $this->whenLoaded('documents', function () {
    return $this->documents->map(function ($document) {
     return [
      'id' => $document->id,
      'title' => $document->title,
      'type' => $document->type,
      'url' => $document->url,
      'file_path' => $document->file_path,
      'project_id' => $document->project_id,
      'notes' => $document->notes,
      'created_at' => $document->created_at,
      'updated_at' => $document->updated_at,
      'updated_by' => $document->updated_by,
      'extra_information' => $document->extra_information,
     ];
    });
   }),
   'important_urls' => $this->whenLoaded('importantUrls', function () {
    return $this->importantUrls->map(function ($importantUrl) {
     return [
      'id' => $importantUrl->id,
      'title' => $importantUrl->title,
      'url' => $importantUrl->url,
      'project_id' => $importantUrl->project_id,
      'client_id' => $importantUrl->client_id,
      'notes' => $importantUrl->notes,
      'created_at' => $importantUrl->created_at,
      'updated_at' => $importantUrl->updated_at,
      'updated_by' => $importantUrl->updated_by,
      'extra_information' => $importantUrl->extra_information,
     ];
    });
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
   'project_count' => $this->project_count,
   'important_url_count' => $this->important_url_count,
  ];
 }
}
