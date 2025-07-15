<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = [
        'title',
        'url',
        'description',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}