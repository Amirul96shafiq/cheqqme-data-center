<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhoneNumber extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'phone',
        'notes',
        'updated_by',
    ];
    protected $dates = ['deleted_at'];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
