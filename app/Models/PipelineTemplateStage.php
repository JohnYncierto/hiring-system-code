<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipelineTemplateStage extends Model
{
    protected $fillable = [
        'pipeline_template_id',
        'name',
        'position',
        'kind',
        'scorecard_template_id',
        'default_duration_minutes',
        'staleness_threshold_days',
    ];

    /**
     * @return BelongsTo<PipelineTemplate, static>
     */
    public function pipelineTemplate(): BelongsTo
    {
        return $this->belongsTo(PipelineTemplate::class);
    }

    /**
     * @return BelongsTo<ScorecardTemplate, static>
     */
    public function scorecardTemplate(): BelongsTo
    {
        return $this->belongsTo(ScorecardTemplate::class);
    }
}
