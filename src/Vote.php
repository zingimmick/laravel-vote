<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LaravelInteraction\Support\InteractionList;
use LaravelInteraction\Support\Models\Interaction;
use LaravelInteraction\Vote\Events\VoteCanceled;
use LaravelInteraction\Vote\Events\Voted;

/**
 * @property \Illuminate\Database\Eloquent\Model $user
 * @property \Illuminate\Database\Eloquent\Model $voter
 * @property \Illuminate\Database\Eloquent\Model $voteable
 * @property bool $upvote
 *
 * @method static \LaravelInteraction\Vote\Vote|\Illuminate\Database\Eloquent\Builder withType(string $type)
 * @method static \LaravelInteraction\Vote\Vote|\Illuminate\Database\Eloquent\Builder query()
 */
class Vote extends Interaction
{
    protected $interaction = InteractionList::VOTE;

    protected $tableNameKey = 'votes';

    protected $morphTypeName = 'voteable';

    protected $dispatchesEvents = [
        'saved' => Voted::class,
        'deleted' => VoteCanceled::class,
    ];

    protected $casts = [
        'upvote' => 'bool',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function voteable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voter(): BelongsTo
    {
        return $this->user();
    }

    public function isVotedBy(Model $user): bool
    {
        return $user->is($this->voter);
    }

    public function isVotedTo(Model $object): bool
    {
        return $object->is($this->voteable);
    }

    public function isUpvote(): bool
    {
        return $this->upvote;
    }

    public function isDownvote(): bool
    {
        return ! $this->isUpvote();
    }
}
