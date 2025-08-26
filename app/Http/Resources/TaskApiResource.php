<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskApiResource extends JsonResource
{
  public function toArray($request)
  {
    // helper to load full model data for IDs or arrays of IDs
    $loadOneOrMany = function ($modelClass, $value) {
      if (empty($value)) {
        return null;
      }
      if (is_array($value)) {
        return $modelClass::whereIn('id', $value)->get()->toArray();
      }
      $model = $modelClass::find($value);
      return $model ? $model->toArray() : null;
    };

    $client = $loadOneOrMany(\App\Models\Client::class, $this->client);
    $project = $loadOneOrMany(\App\Models\Project::class, $this->project);
    $document = $loadOneOrMany(\App\Models\Document::class, $this->document);
    $importantUrl = $loadOneOrMany(\App\Models\ImportantUrl::class, $this->important_url);

    return [
      'id' => $this->id,
      'title' => $this->title,
      'description' => $this->description,
      'status' => $this->status,
      'due_date' => $this->due_date,
      'assigned_to' => $this->assigned_to,
      'updated_by' => $this->updated_by,
      'created_at' => $this->created_at,
      'client' => $client,
      'project' => $project,
      'document' => $document,
      'important_url' => $importantUrl,
    ];
  }
}


