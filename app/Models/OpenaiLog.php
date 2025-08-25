<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenaiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'model',
        'endpoint',
        'request_payload',
        'response_text',
        'status_code',
        'duration_ms',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_text' => 'string',
    ];
}
