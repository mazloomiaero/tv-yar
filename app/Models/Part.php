<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PartFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'code',
    'default_unit_price',
    'unit',
    'status',
])]
class Part extends Model
{
    /** @use HasFactory<PartFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return HasMany<ServiceReportPart, $this>
     */
    public function reportParts(): HasMany
    {
        return $this->hasMany(ServiceReportPart::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_unit_price' => 'decimal:2',
        ];
    }
}
