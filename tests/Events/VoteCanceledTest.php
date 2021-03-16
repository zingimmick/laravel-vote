<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote\Tests\Events;

use Illuminate\Support\Facades\Event;
use LaravelInteraction\Vote\Events\VoteCanceled;
use LaravelInteraction\Vote\Tests\Models\Channel;
use LaravelInteraction\Vote\Tests\Models\User;
use LaravelInteraction\Vote\Tests\TestCase;

class VoteCanceledTest extends TestCase
{
    public function testCancelVote(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        Event::fake([VoteCanceled::class]);
        $user->vote($channel);
        $user->cancelVote($channel);
        Event::assertDispatchedTimes(VoteCanceled::class);
    }

    public function testCancelVoteTimes(): void
    {
        $user = User::query()->create();
        $channel = Channel::query()->create();
        Event::fake([VoteCanceled::class]);
        $user->vote($channel);
        $user->cancelVote($channel);
        $user->cancelVote($channel);
        $user->cancelVote($channel);
        Event::assertDispatchedTimes(VoteCanceled::class);
    }
}
