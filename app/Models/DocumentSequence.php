<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'document_type',
    'prefix',
    'year',
    'current_number',
])]
class DocumentSequence extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'current_number' => 'integer',
        ];
    }
}
