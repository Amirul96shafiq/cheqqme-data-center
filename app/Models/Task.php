<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Task extends Model
{
  use HasFactory, SoftDeletes, LogsActivity;

  protected $fillable = [
    'title',
    'description',
    'assigned_to',
    'status',
    'order_column',
    'due_date',
    'updated_by',
    'extra_information',
  ];

  public function getActivityLogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly([
        'title',
        'description',
        'assigned_to',
        'status',
        'order_column',
        'due_date',
        'created_at',
        'extra_information',
        'updated_at',
        'updated_by',
      ])
      ->useLogName('Tasks');
  }

  protected $casts = [
    'extra_information' => 'array',
  ];

  public function updatedBy()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  public function assignedTo()
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }
}