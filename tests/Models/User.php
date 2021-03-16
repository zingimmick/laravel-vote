<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LaravelInteraction\Vote\Concerns\Voteable;
use LaravelInteraction\Vote\Concerns\Voter;

/**
 * @method static \LaravelInteraction\Vote\Tests\Models\User|\Illuminate\Database\Eloquent\Builder query()
 */
class User extends Model
{
    use Voter;
    use Voteable;

    public function votedChannels(): MorphToMany
    {
        return $this->votedItems(Channel::class);
    }

    public function upvotedChannels(): MorphToMany
    {
        return $this->upvotedItems(Channel::class);
    }

    public function downvotedChannels(): MorphToMany
    {
        return $this->downvotedItems(Channel::class);
    }
}
