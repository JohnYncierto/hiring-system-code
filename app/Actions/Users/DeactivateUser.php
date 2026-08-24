<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Services\ActivityLogger;

class DeactivateUser
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    /**
     * Deactivate a user. Never deletes — deactivation is reversible.
     *
     * Warns the caller (via the returned array) if the user has upcoming
     * interviews that will need reassignment. That check is a no-op until
     * the interviews table exists (1c) — see the TODO below.
     *
     * @return array{warnings: array<string>}
     */
    public function handle(User $target, User $actor): array
    {
        $target->update(['deactivated_at' => now()]);

        $this->activityLogger->log(
            action: 'user.deactivated',
            subject: $target,
        );

        return [
            'warnings' => $this->upcomingInterviewWarnings($target),
        ];
    }

    /**
     * TODO(1c): once interviews exist, check whether $target has upcoming
     * scheduled interviews and return a warning listing them, per PRD
     * requirement #4 — deactivation must warn, not block, and never
     * silently orphan an interview panel.
     *
     * @return array<string>
     */
    protected function upcomingInterviewWarnings(User $target): array
    {
        return [];
    }
}
