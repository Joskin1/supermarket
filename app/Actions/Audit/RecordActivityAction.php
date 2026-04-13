<?php

namespace App\Actions\Audit;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class RecordActivityAction
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function execute(
        string $event,
        string $description,
        ?Model $subject = null,
        array $properties = [],
        User|int|null $actor = null,
    ): ActivityLog {
        $actorId = match (true) {
            $actor instanceof User => $actor->getKey(),
            is_int($actor) => $actor,
            default => Auth::id(),
        };

        return ActivityLog::query()->create([
            'actor_id' => $actorId,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties ?: null,
        ]);
    }
}
