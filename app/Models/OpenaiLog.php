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
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_text' => 'string',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
    ];
}
