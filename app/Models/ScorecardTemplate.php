<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScorecardTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    /**
     * @return HasMany<ScorecardTemplateCriterion, static>
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(ScorecardTemplateCriterion::class)->orderBy('position');
    }
}
