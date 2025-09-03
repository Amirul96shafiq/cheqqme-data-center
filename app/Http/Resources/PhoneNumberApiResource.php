<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PhoneNumberApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'extra_information' => $this->extra_information,
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
            'has_notes' => !empty($this->notes),
            'phone_format' => $this->getPhoneFormat(),
        ];
    }

    private function getPhoneFormat(): string
    {
        $phone = $this->phone;
        
        if (str_contains($phone, '+')) {
            return 'international';
        }
        
        if (str_starts_with($phone, '01')) {
            return 'mobile';
        }
        
        if (str_starts_with($phone, '03') || str_starts_with($phone, '04') || 
            str_starts_with($phone, '05') || str_starts_with($phone, '06') || 
            str_starts_with($phone, '07') || str_starts_with($phone, '08') || 
            str_starts_with($phone, '09')) {
            return 'landline';
        }
        
        return 'unknown';
    }
}
