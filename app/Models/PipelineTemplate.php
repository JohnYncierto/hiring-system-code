<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipelineTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PipelineTemplateStage, static>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(PipelineTemplateStage::class)->orderBy('position');
    }
}
