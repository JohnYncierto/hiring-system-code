<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

class ActivityLogEntry extends Model
{
    /**
     * This table has no updated_at column — entries are append-only.
     */
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'subject_type', 'subject_id', 'action', 'reason', 'metadata'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, static>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, static>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Activity log entries are append-only — updates are never permitted.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new LogicException('Activity log entries cannot be updated. The log is append-only.');
    }

    /**
     * Activity log entries are append-only — deletion is never permitted.
     */
    public function delete(): ?bool
    {
        throw new LogicException('Activity log entries cannot be deleted. The log is append-only.');
    }
}
