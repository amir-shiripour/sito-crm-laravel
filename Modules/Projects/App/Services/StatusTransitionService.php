<?php

namespace Modules\Projects\App\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Projects\App\Exceptions\InvalidStatusTransitionException;
use Modules\Projects\App\Http\Models\ProjectStatus;

class StatusTransitionService
{

    public function canTransition(?ProjectStatus $from, ProjectStatus $to): bool
    {
        if ($from === null) {
            return true;
        }

        if ($from->id === $to->id) {
            return true;
        }

        $allowed = $from->allowed_transitions;

        if (empty($allowed)) {
            return true;
        }

        return in_array($to->id, $allowed, true);
    }

    /**
     *
     * @throws InvalidStatusTransitionException
     */
    public function assertCanTransition(?ProjectStatus $from, ProjectStatus $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new InvalidStatusTransitionException(
                $from?->name ?? 'بدون وضعیت',
                $to->name
            );
        }
    }

    /**
     *
     * @return Collection
     */
    public function allowedTargets(?ProjectStatus $from, string $type): Collection
    {
        $all = ProjectStatus::forType($type)->get();

        if ($from === null || empty($from->allowed_transitions)) {
            return $all;
        }

        return $all->whereIn('id', $from->allowed_transitions)->values();
    }
}
