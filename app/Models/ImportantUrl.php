<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportantUrl extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'url',
        'project_id',
        'client_id',
        'notes',
        'updated_by',
        'extra_information',
    ];
    protected $casts = [
        'extra_information' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
