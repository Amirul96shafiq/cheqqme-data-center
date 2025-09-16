<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotBackup extends Model
{
    protected $fillable = [
        'user_id',
        'backup_name',
        'backup_data',
        'backup_type',
        'message_count',
        'backup_date',
        'conversation_start_date',
        'conversation_end_date',
        'file_name',
    ];

    protected $casts = [
        'backup_data' => 'array',
        'backup_date' => 'datetime',
        'conversation_start_date' => 'datetime',
        'conversation_end_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedBackupDateAttribute(): string
    {
        return $this->backup_date->format('j/n/y, h:i A');
    }

    public function getFormattedDateRangeAttribute(): string
    {
        $start = $this->conversation_start_date->format('j/n/y, h:i A');
        $end = $this->conversation_end_date->format('j/n/y, h:i A');

        return "{$start} - {$end}";
    }

    public function getFileSizeAttribute(): string
    {
        $size = strlen(json_encode($this->backup_data));
        if ($size < 1024) {
            return $size.' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2).' KB';
        } else {
            return round($size / 1048576, 2).' MB';
        }
    }
}
