<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'project_url',
        'client_id',
        'description',
        'status',
        'notes',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
