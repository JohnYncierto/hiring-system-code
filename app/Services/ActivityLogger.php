<?php

namespace App\Services;

use App\Models\ActivityLogEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Record a state change to the append-only activity log.
     */
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function log(string $action, Model $subject, ?string $reason = null, array $metadata = []): ActivityLogEntry
    {
        /** @var User|null $actor */
        $actor = Auth::user();

        return ActivityLogEntry::create([
            'user_id' => $actor?->id,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }
}
