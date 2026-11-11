<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Installation notifications table — not Laravel's morph database notifications.
 */
#[Fillable([
    'name',
])]
class Notification extends Model {}
