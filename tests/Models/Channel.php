<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelInteraction\Vote\Concerns\Voteable;

/**
 * @method static \LaravelInteraction\Vote\Tests\Models\Channel|\Illuminate\Database\Eloquent\Builder query()
 */
class Channel extends Model
{
    use Voteable;
}
