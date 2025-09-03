<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TrelloBoardApiResource extends JsonResource
{
 public function toArray($request)
 {
  return [
   'id' => $this->id,
   'name' => $this->name,
   'url' => $this->url,
   'notes' => $this->notes,
   'show_on_boards' => $this->show_on_boards,
   'created_by' => $this->created_by,
   'updated_by' => $this->updated_by,
   'created_at' => $this->created_at,
   'updated_at' => $this->updated_at,
   'deleted_at' => $this->deleted_at,
   'extra_information' => $this->extra_information,
   // Include related data if loaded
   'creator' => $this->whenLoaded('creator', function () {
    return $this->creator ? [
     'id' => $this->creator->id,
     'name' => $this->creator->name,
     'email' => $this->creator->email,
     'username' => $this->creator->username,
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
  ];
 }
}
