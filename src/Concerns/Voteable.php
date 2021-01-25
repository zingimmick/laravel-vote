<?php

declare(strict_types=1);

namespace Zing\LaravelVote\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use function is_a;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\Zing\LaravelVote\Vote[] $votes
 * @property-read \Illuminate\Database\Eloquent\Collection|\Zing\LaravelVote\Concerns\Voter[] $voters
 * @property-read \Illuminate\Database\Eloquent\Collection|\Zing\LaravelVote\Concerns\Voter[] $upvoters
 * @property-read \Illuminate\Database\Eloquent\Collection|\Zing\LaravelVote\Concerns\Voter[] $downvoters
 * @property-read int|null $voters_count
 * @property-read int|null $upvoters_count
 * @property-read int|null $downvoters_count
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
    public function isUpvotedBy(Model $user): bool
    {
        if (! is_a($user, config('vote.models.user'))) {
            return false;
        }

        if ($this->relationLoaded('upvoters')) {
            return $this->upvoters->contains($user);
        }

        return ($this->relationLoaded('votes') ? $this->votes : $this->votes())
            ->where(config('vote.column_names.user_foreign_key'), $user->getKey())->where('upvote', true)->count() > 0;
    }

    public function isDownvotedBy(Model $user): bool
    {
        if (! is_a($user, config('vote.models.user'))) {
            return false;
        }

        if ($this->relationLoaded('downvoters')) {
            return $this->downvoters->contains($user);
        }

        return ($this->relationLoaded('votes') ? $this->votes : $this->votes())
            ->where(config('vote.column_names.user_foreign_key'), $user->getKey())->where('upvote', false)->count() > 0;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return bool
     */
    public function isVotedBy(Model $user): bool
    {
        if (! is_a($user, config('vote.models.user'))) {
            return false;
        }

        if ($this->relationLoaded('voters')) {
            return $this->voters->contains($user);
        }

        return ($this->relationLoaded('votes') ? $this->votes : $this->votes())
            ->where(config('vote.column_names.user_foreign_key'), $user->getKey())->count() > 0;
    }

    public function isNotVotedBy(Model $user): bool
    {
        return ! $this->isVotedBy($user);
    }

    public function isNotUpvotedBy(Model $user): bool
    {
        return ! $this->isUpvotedBy($user);
    }

    public function isNotDownvotedBy(Model $user): bool
    {
        return ! $this->isDownvotedBy($user);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function votes(): MorphMany
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
        )->withTimestamps()->withPivot('upvote');
    }

    public function upvoters(): MorphToMany
    {
        return $this->voters()->wherePivot('upvote', true);
    }

    public function downvoters(): MorphToMany
    {
        return $this->voters()->wherePivot('upvote', false);
    }

    public function votersCount(): int
    {
        if ($this->voters_count !== null) {
            return (int) $this->voters_count;
        }

        $this->loadCount('voters');

        return (int) $this->voters_count;
    }

    public function upvotersCount(): int
    {
        if ($this->upvoters_count !== null) {
            return (int) $this->upvoters_count;
        }

        $this->loadCount('upvoters');

        return (int) $this->upvoters_count;
    }

    public function downvotersCount(): int
    {
        if ($this->downvoters_count !== null) {
            return (int) $this->downvoters_count;
        }

        $this->loadCount('downvoters');

        return (int) $this->downvoters_count;
    }

    public function votersCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return $this->countForHuman($this->votersCount(), $precision, $mode, $divisors);
    }

    public function upvotersCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return $this->countForHuman($this->upvotersCount(), $precision, $mode, $divisors);
    }

    public function downvotersCountForHumans($precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        return $this->countForHuman($this->downvotersCount(), $precision, $mode, $divisors);
    }

    protected function countForHuman(int $number, $precision = 1, $mode = PHP_ROUND_HALF_UP, $divisors = null): string
    {
        $divisors = collect($divisors ?? config('vote.divisors'));
        $divisor = $divisors->keys()->filter(
            function ($divisor) use ($number) {
                return $divisor <= abs($number);
            }
        )->last(null, 1);

        if ($divisor === 1) {
            return (string) $number;
        }

        return number_format(round($number / $divisor, $precision, $mode), $precision) . $divisors->get($divisor);
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

    public function scopeWhereNotUpvotedBy(Builder $query, Model $user): Builder
    {
        return $query->whereDoesntHave(
            'upvoters',
            function (Builder $query) use ($user) {
                return $query->whereKey($user->getKey());
            }
        );
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
}