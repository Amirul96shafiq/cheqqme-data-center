<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserApiResource extends JsonResource
{
 public function toArray($request)
 {
  return [
   'id' => $this->id,
   'username' => $this->username,
   'name' => $this->name,
   'email' => $this->email,
   'timezone' => $this->timezone,
   'avatar' => $this->avatar,
   'cover_image' => $this->cover_image,
   'email_verified_at' => $this->email_verified_at,
   'api_key_generated_at' => $this->api_key_generated_at,
   'updated_by' => $this->updated_by,
   'created_at' => $this->created_at,
   'updated_at' => $this->updated_at,
   'deleted_at' => $this->deleted_at,
   // Include related data if loaded
   'updated_by_user' => $this->whenLoaded('updatedBy', function () {
    return $this->updatedBy ? [
     'id' => $this->updatedBy->id,
     'name' => $this->updatedBy->name,
     'email' => $this->updatedBy->email,
     'username' => $this->updatedBy->username,
    ] : null;
   }),
   // Computed attributes
   'has_api_key' => $this->hasApiKey(),
   'masked_api_key' => $this->getMaskedApiKey(),
   'short_name' => $this->short_name,
   'has_avatar' => !empty($this->avatar),
   'has_cover_image' => !empty($this->cover_image),
   'email_verified' => !empty($this->email_verified_at),
   'avatar_url' => $this->getFilamentAvatarUrl(),
   'cover_image_url' => $this->getFilamentCoverImageUrl(),
  ];
 }
}
