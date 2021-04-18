<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelInteraction\Vote\Vote;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelInteraction\Vote\Vote[] $voterVotes
 * @property-read int|null $voter_votes_count
 */
trait Voter
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return bool
     */
    public function cancelVote(Model $object): bool
    {
        $hasNotVoted = $this->hasNotVoted($object);
        if ($hasNotVoted) {
            return true;
        }
        $voterVotesLoaded = $this->relationLoaded('voterVotes');
        if ($voterVotesLoaded) {
            $this->unsetRelation('voterVotes');
        }

        return (bool) $this->votedItems(get_class($object))
            ->detach($object->getKey());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return \LaravelInteraction\Vote\Vote
     */
    public function downvote(Model $object): Vote
    {
        return $this->vote($object, false);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return bool
     */
    public function hasDownvoted(Model $object): bool
    {
        return ($this->relationLoaded('voterVotes') ? $this->voterVotes : $this->voterVotes())
            ->where('voteable_id', $object->getKey())
            ->where('voteable_type', $object->getMorphClass())
            ->where('upvote', false)
            ->count() > 0;
    }

    public function hasNotDownvoted(Model $object): bool
    {
        return ! $this->hasDownvoted($object);
    }

    public function hasNotUpvoted(Model $object): bool
    {
        return ! $this->hasUpvoted($object);
    }

    public function hasNotVoted(Model $object): bool
    {
        return ! $this->hasVoted($object);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return bool
     */
    public function hasUpvoted(Model $object): bool
    {
        return ($this->relationLoaded('voterVotes') ? $this->voterVotes : $this->voterVotes())
            ->where('voteable_id', $object->getKey())
            ->where('voteable_type', $object->getMorphClass())
            ->where('upvote', true)
            ->count() > 0;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return bool
     */
    public function hasVoted(Model $object): bool
    {
        return ($this->relationLoaded('voterVotes') ? $this->voterVotes : $this->voterVotes())
            ->where('voteable_id', $object->getKey())
            ->where('voteable_type', $object->getMorphClass())
            ->count() > 0;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return \LaravelInteraction\Vote\Vote
     */
    public function upvote(Model $object): Vote
    {
        return $this->vote($object);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     * @param bool $upvote
     *
     * @return \LaravelInteraction\Vote\Vote
     */
    public function vote(Model $object, $upvote = true): Vote
    {
        $attributes = [
            'voteable_id' => $object->getKey(),
            'voteable_type' => $object->getMorphClass(),
        ];
        $values = [
            'upvote' => $upvote,
        ];
        $vote = $this->voterVotes()
            ->where($attributes)
            ->firstOrNew($attributes, $values);
        $vote->fill($values);
        if ($vote->isDirty() || ! $vote->exists) {
            $voterVotesLoaded = $this->relationLoaded('voterVotes');
            if ($voterVotesLoaded) {
                $this->unsetRelation('voterVotes');
            }
            $vote->save();
        }

        return $vote;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function voterVotes(): HasMany
    {
        return $this->hasMany(
            config('vote.models.vote'),
            config('vote.column_names.user_foreign_key'),
            $this->getKeyName()
        );
    }

    /**
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    protected function downvotedItems(string $class): MorphToMany
    {
        return $this->votedItems($class)
            ->wherePivot('upvote', false);
    }

    /**
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    protected function upvotedItems(string $class): MorphToMany
    {
        return $this->votedItems($class)
            ->wherePivot('upvote', true);
    }

    /**
     * @param string $class
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    protected function votedItems(string $class): MorphToMany
    {
        return $this->morphedByMany(
            $class,
            'voteable',
            config('vote.models.vote'),
            config('vote.column_names.user_foreign_key')
        )
            ->withTimestamps()
            ->withPivot('upvote');
    }
}
