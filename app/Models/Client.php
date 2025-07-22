<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pic_name',
        'pic_email',
        'pic_contact_number',
        'company_name',
        'company_website_url',
        'company_address',
        'billing_address',
    ];
}
