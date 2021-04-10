<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelInteraction\Support\Interaction;
use function is_a;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Vote\Vote[] $voteableVotes
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Vote\Concerns\Voter[] $voters
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Vote\Concerns\Voter[] $upvoters
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Vote\Concerns\Voter[] $downvoters
 * @property-read string|int|null $voters_count
 * @property-read string|int|null $upvoters_count
 * @property-read string|int|null $downvoters_count
 *
 * @method static static|\Illuminate\Database\Eloquent\Builder whereVotedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder whereNotVotedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder whereUpvotedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder whereNotUpvotedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder whereDownvotedBy(\Illuminate\Database\Eloquent\Model $user)
 * @method static static|\Illuminate\Database\Eloquent\Builder whereNotDownvotedBy(\Illuminate\Database\Eloquent\Model $user)
 */
trait Voteable
{
    public function downvoters(): MorphToMany
    {
        return $this->voters()
            ->wherePivot('upvote', false);
    }

    public function downvotersCount(): int
    {
        if ($this->downvoters_count !== null) {
            return (int) $this->downvoters_count;
        }

        $this->loadCount('downvoters');

        return (int) $this->downvoters_count;
    }

    public function downvotersCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->downvotersCount(),
            $precision,
            $mode,
            $divisors ?? config('vote.divisors')
        );
    }

    public function isDownvotedBy(Model $user): bool
    {
        $thisIsVoter = $this->isVoter($user);
        if (! $thisIsVoter) {
            return false;
        }
        $downvotersThisRelationLoaded = $this->relationLoaded('downvoters');

        if ($downvotersThisRelationLoaded) {
            return $this->downvoters->contains($user);
        }

        return ($this->relationLoaded('votes') ? $this->voteableVotes : $this->voteableVotes())
            ->where(config('vote.column_names.user_foreign_key'), $user->getKey())
            ->where('upvote', false)
            ->count() > 0;
    }

    public function isNotDownvotedBy(Model $user): bool
    {
        return ! $this->isDownvotedBy($user);
    }

    public function isNotUpvotedBy(Model $user): bool
    {
        return ! $this->isUpvotedBy($user);
    }

    public function isNotVotedBy(Model $user): bool
    {
        return ! $this->isVotedBy($user);
    }

    public function isUpvotedBy(Model $user): bool
    {
        $thisIsVoter = $this->isVoter($user);
        if (! $thisIsVoter) {
            return false;
        }
        $upvotersThisRelationLoaded = $this->relationLoaded('upvoters');

        if ($upvotersThisRelationLoaded) {
            return $this->upvoters->contains($user);
        }

        return ($this->relationLoaded('voteableVotes') ? $this->voteableVotes : $this->voteableVotes())
            ->where(config('vote.column_names.user_foreign_key'), $user->getKey())
            ->where('upvote', true)
            ->count() > 0;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return bool
     */
    public function isVotedBy(Model $user): bool
    {
        $thisIsVoter = $this->isVoter($user);
        if (! $thisIsVoter) {
            return false;
        }
        $votersThisRelationLoaded = $this->relationLoaded('voters');

        if ($votersThisRelationLoaded) {
            return $this->voters->contains($user);
        }

        return ($this->relationLoaded('votes') ? $this->voteableVotes : $this->voteableVotes())
            ->where(config('vote.column_names.user_foreign_key'), $user->getKey())
            ->count() > 0;
    }

    public function scopeWhereDownvotedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas(
            'downvoters',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWhereNotDownvotedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave(
            'downvoters',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWhereNotUpvotedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave(
            'upvoters',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWhereNotVotedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave(
            'voters',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWhereUpvotedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas(
            'upvoters',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function scopeWhereVotedBy(Builder $query, Model $user): Builder
    {
        return $query->whereHas(
            'voters',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
    }

    public function upvoters(): MorphToMany
    {
        return $this->voters()
            ->wherePivot('upvote', true);
    }

    public function upvotersCount(): int
    {
        if ($this->upvoters_count !== null) {
            return (int) $this->upvoters_count;
        }

        $this->loadCount('upvoters');

        return (int) $this->upvoters_count;
    }

    public function upvotersCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->upvotersCount(),
            $precision,
            $mode,
            $divisors ?? config('vote.divisors')
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function voteableVotes(): MorphMany
    {
        return $this->morphMany(config('vote.models.vote'), 'voteable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function voters(): MorphToMany
    {
        return $this->morphToMany(
            config('vote.models.user'),
            'voteable',
            config('vote.models.vote'),
            null,
            config('vote.column_names.user_foreign_key')
        )->withTimestamps()
            ->withPivot('upvote');
    }

    public function votersCount(): int
    {
        if ($this->voters_count !== null) {
            return (int) $this->voters_count;
        }

        $this->loadCount('voters');

        return (int) $this->voters_count;
    }

    public function votersCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return Interaction::numberForHumans(
            $this->votersCount(),
            $precision,
            $mode,
            $divisors ?? config('vote.divisors')
        );
    }

    protected function isVoter($user): bool
    {
        return is_a($user, config('vote.models.user'));
    }
}
