<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote\Tests\Concerns;

use LaravelInteraction\Vote\Tests\Models\Channel;
use LaravelInteraction\Vote\Tests\Models\User;
use LaravelInteraction\Vote\Tests\TestCase;

class VoteableTest extends TestCase
{
    public function modelClasses(): array
    {
        return[[Channel::class], [User::class]];
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testVotes(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->vote($model);
        self::assertSame(1, $model->voteableVotes()->count());
        self::assertSame(1, $model->voteableVotes->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testVotersCount(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->vote($model);
        self::assertSame(1, $model->votersCount());
        $user->cancelVote($model);
        self::assertSame(1, $model->votersCount());
        $model->loadCount('voters');
        self::assertSame(0, $model->votersCount());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testUpvotersCount(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->upvote($model);
        self::assertSame(1, $model->upvotersCount());
        $user->cancelVote($model);
        self::assertSame(1, $model->upvotersCount());
        $model->loadCount('upvoters');
        self::assertSame(0, $model->upvotersCount());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testDownvotersCount(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->downvote($model);
        self::assertSame(1, $model->downvotersCount());
        $user->cancelVote($model);
        self::assertSame(1, $model->downvotersCount());
        $model->loadCount('downvoters');
        self::assertSame(0, $model->downvotersCount());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testVotersCountForHumans(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->vote($model);
        self::assertSame('1', $model->votersCountForHumans());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testUpvotersCountForHumans(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->upvote($model);
        self::assertSame('1', $model->upvotersCountForHumans());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testDownvotersCountForHumans(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->downvote($model);
        self::assertSame('1', $model->downvotersCountForHumans());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testIsVotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertFalse($model->isVotedBy($model));
        $user->vote($model);
        self::assertTrue($model->isVotedBy($user));
        $model->load('voters');
        $user->cancelVote($model);
        self::assertTrue($model->isVotedBy($user));
        $model->load('voters');
        self::assertFalse($model->isVotedBy($user));
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testIsNotVotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertTrue($model->isNotVotedBy($model));
        $user->vote($model);
        self::assertFalse($model->isNotVotedBy($user));
        $model->load('voters');
        $user->cancelVote($model);
        self::assertFalse($model->isNotVotedBy($user));
        $model->load('voters');
        self::assertTrue($model->isNotVotedBy($user));
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testIsUpvotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertFalse($model->isVotedBy($model));
        $user->upvote($model);
        self::assertTrue($model->isUpvotedBy($user));
        $model->load('upvoters');
        $user->cancelVote($model);
        self::assertTrue($model->isUpvotedBy($user));
        $model->load('upvoters');
        self::assertFalse($model->isUpvotedBy($user));
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testIsNotUpvotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertTrue($model->isNotUpvotedBy($model));
        $user->vote($model);
        self::assertFalse($model->isNotUpvotedBy($user));
        $model->load('upvoters');
        $user->cancelVote($model);
        self::assertFalse($model->isNotUpvotedBy($user));
        $model->load('upvoters');
        self::assertTrue($model->isNotUpvotedBy($user));
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testIsDownvotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertFalse($model->isDownvotedBy($model));
        $user->downvote($model);
        self::assertTrue($model->isDownvotedBy($user));
        $model->load('downvoters');
        $user->cancelVote($model);
        self::assertTrue($model->isDownvotedBy($user));
        $model->load('downvoters');
        self::assertFalse($model->isDownvotedBy($user));
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testIsNotDownvotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        self::assertTrue($model->isNotDownvotedBy($model));
        $user->downvote($model);
        self::assertFalse($model->isNotDownvotedBy($user));
        $model->load('downvoters');
        $user->cancelVote($model);
        self::assertFalse($model->isNotDownvotedBy($user));
        $model->load('downvoters');
        self::assertTrue($model->isNotDownvotedBy($user));
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testVoters(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->vote($model);
        self::assertSame(1, $model->voters()->count());
        $user->cancelVote($model);
        self::assertSame(0, $model->voters()->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testUpvoters(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->upvote($model);
        self::assertSame(1, $model->upvoters()->count());
        $user->cancelVote($model);
        self::assertSame(0, $model->upvoters()->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testDownvoters(string $modelClass): void
    {
        $user = User::query()->create();
        $model = $modelClass::query()->create();
        $user->downvote($model);
        self::assertSame(1, $model->downvoters()->count());
        $user->cancelVote($model);
        self::assertSame(0, $model->downvoters()->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testScopeWhereVotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->vote($model);
        self::assertSame(1, $modelClass::query()->whereVotedBy($user)->count());
        self::assertSame(0, $modelClass::query()->whereVotedBy($other)->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testScopeWhereNotVotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->vote($model);
        self::assertSame(
            $modelClass::query()->whereKeyNot($model->getKey())->count(),
            $modelClass::query()->whereNotVotedBy($user)->count()
        );
        self::assertSame($modelClass::query()->count(), $modelClass::query()->whereNotVotedBy($other)->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testScopeWhereUpvotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->upvote($model);
        self::assertSame(1, $modelClass::query()->whereUpvotedBy($user)->count());
        self::assertSame(0, $modelClass::query()->whereUpvotedBy($other)->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testScopeWhereNotUpvotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->upvote($model);
        self::assertSame(
            $modelClass::query()->whereKeyNot($model->getKey())->count(),
            $modelClass::query()->whereNotUpvotedBy($user)->count()
        );
        self::assertSame($modelClass::query()->count(), $modelClass::query()->whereNotUpvotedBy($other)->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testScopeWhereDownvotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->downvote($model);
        self::assertSame(1, $modelClass::query()->whereDownVotedBy($user)->count());
        self::assertSame(0, $modelClass::query()->whereDownVotedBy($other)->count());
    }

    /**
     * @dataProvider modelClasses
     *
     * @param \LaravelInteraction\Vote\Tests\Models\User|\LaravelInteraction\Vote\Tests\Models\Channel|string $modelClass
     */
    public function testScopeWhereNotDownvotedBy(string $modelClass): void
    {
        $user = User::query()->create();
        $other = User::query()->create();
        $model = $modelClass::query()->create();
        $user->downvote($model);
        self::assertSame(
            $modelClass::query()->whereKeyNot($model->getKey())->count(),
            $modelClass::query()->whereNotDownvotedBy($user)->count()
        );
        self::assertSame($modelClass::query()->count(), $modelClass::query()->whereNotDownvotedBy($other)->count());
    }
}
