<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote\Tests\Events;

use Illuminate\Support\Facades\Event;
use LaravelInteraction\Vote\Events\Voted;
use LaravelInteraction\Vote\Tests\Models\Channel;
use LaravelInteraction\Vote\Tests\Models\User;
use LaravelInteraction\Vote\Tests\TestCase;

class VotedTest extends TestCase
{
    public function testVote(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        Event::fake([Voted::class]);
        $user->vote($channel);
        Event::assertDispatchedTimes(Voted::class);
    }

    public function testVoteTimes(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        Event::fake([Voted::class]);
        $user->vote($channel);
        $user->vote($channel);
        $user->vote($channel);
        Event::assertDispatchedTimes(Voted::class);
    }

    public function testDownVoteAfterUpvote(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        Event::fake([Voted::class]);
        $user->vote($channel);
        $user->downvote($channel);
        Event::assertDispatchedTimes(Voted::class, 2);
    }
}
