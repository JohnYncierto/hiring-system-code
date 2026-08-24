<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScorecardTemplateCriterion extends Model
{
    protected $fillable = ['scorecard_template_id', 'name', 'description', 'position'];

    /**
     * @return BelongsTo<ScorecardTemplate, static>
     */
    public function scorecardTemplate(): BelongsTo
    {
        return $this->belongsTo(ScorecardTemplate::class);
    }
}
