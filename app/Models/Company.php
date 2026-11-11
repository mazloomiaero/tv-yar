<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'trade_name',
    'logo',
    'phone',
    'mobile',
    'email',
    'website',
    'province',
    'city',
    'address',
    'postal_code',
    'invoice_title',
    'invoice_description',
    'primary_color',
    'secondary_color',
    'status',
])]
class Company extends Model
{
    protected $table = 'company';
}
