# Laravel Vote

User upvote/downvote behaviour for Laravel.

<p align="center">
<a href="https://packagist.org/packages/laravel-interaction/vote"><img src="https://poser.pugx.org/laravel-interaction/vote/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel-interaction/vote"><img src="https://poser.pugx.org/laravel-interaction/vote/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel-interaction/vote"><img src="https://poser.pugx.org/laravel-interaction/vote/v/unstable.svg" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/laravel-interaction/vote"><img src="https://poser.pugx.org/laravel-interaction/vote/license" alt="License"></a>
</p>

> **Requires [PHP 7.2.0+](https://php.net/releases/)**

Require Laravel Vote using [Composer](https://getcomposer.org):

```bash
composer require laravel-interaction/vote
```

## Usage

### Setup Voter

```php
use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Vote\Concerns\Voter;

class User extends Model
{
    use Voter;
}
```

### Setup Voteable

```php
use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Vote\Concerns\Voteable;

class Channel extends Model
{
    use Voteable;
}
```

### Voter

```php
use LaravelInteraction\Vote\Tests\Models\Channel;
/** @var \LaravelInteraction\Vote\Tests\Models\User $user */
/** @var \LaravelInteraction\Vote\Tests\Models\Channel $channel */
// Vote to Voteable
$user->vote($channel);
$user->upvote($channel);
$user->downvote($channel);
$user->cancelVote($channel);

// Compare Voteable
$user->hasVoted($channel);
$user->hasNotVoted($channel);
$user->hasUpvoted($channel);
$user->hasNotUpvoted($channel);
$user->hasDownvoted($channel);
$user->hasNotDownvoted($channel);

// Get voted info
$user->voterVotes()->count(); 

// with type
$user->voterVotes()->withType(Channel::class)->count(); 
$user->votedChannels()->count();
$user->upvotedChannels()->count();
$user->downvotedChannels()->count();

// get voted channels
Channel::query()->whereVotedBy($user)->get();
Channel::query()->whereUpvotedBy($user)->get();
Channel::query()->whereDownvotedBy($user)->get();

// get voted channels doesnt voted
Channel::query()->whereNotVotedBy($user)->get();
Channel::query()->whereNotUpvotedBy($user)->get();
Channel::query()->whereNotDownvotedBy($user)->get();
```

### Voteable

```php
use LaravelInteraction\Vote\Tests\Models\User;
use LaravelInteraction\Vote\Tests\Models\Channel;
/** @var \LaravelInteraction\Vote\Tests\Models\User $user */
/** @var \LaravelInteraction\Vote\Tests\Models\Channel $channel */
// Compare Voter
$channel->isVotedBy($user); 
$channel->isNotVotedBy($user);
$channel->isUpvotedBy($user); 
$channel->isNotUpvotedBy($user);
$channel->isDownvotedBy($user); 
$channel->isNotDownvotedBy($user);
// Get voters info
$channel->voters->each(function (User $user){
    echo $user->getKey();
});
$channel->upvoters->each(function (User $user){
    echo $user->getKey();
});
$channel->downvoters->each(function (User $user){
    echo $user->getKey();
});

$channels = Channel::query()->withCount('voters')->get();
$channels->each(function (Channel $channel){
    echo $channel->voters()->count(); // 1100
    echo $channel->voters_count; // "1100"
    echo $channel->votersCount(); // 1100
    echo $channel->votersCountForHumans(); // "1.1K"
    echo $channel->upvoters()->count(); // 1100
    echo $channel->upvoters_count; // "1100"
    echo $channel->upvotersCount(); // 1100
    echo $channel->upvotersCountForHumans(); // "1.1K"
    echo $channel->downvoters()->count(); // 1100
    echo $channel->downvoters_count; // "1100"
    echo $channel->downvotersCount(); // 1100
    echo $channel->downvotersCountForHumans(); // "1.1K"
});
```

### Events

| Event | Fired |
| --- | --- |
| `LaravelInteraction\Vote\Events\Voted` | When an object get voted/upvoted/downvoted. |
| `LaravelInteraction\Vote\Events\VoteCanceled` | When an object get vote cancellation. |

## License

Laravel Vote is an open-sourced software licensed under the [MIT license](LICENSE).
