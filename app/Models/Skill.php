<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsToMany<Technician, $this>
     */
    public function technicians(): BelongsToMany
    {
        return $this->belongsToMany(Technician::class, 'technician_skills');
    }
}
