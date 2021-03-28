<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LaravelInteraction\Vote\Tests\Models\User;
use LaravelInteraction\Vote\VoteServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        Schema::create(
            'users',
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->timestamps();
            }
        );
        Schema::create(
            'channels',
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->timestamps();
            }
        );
    }

    protected function getEnvironmentSetUp($app): void
    {
        config([
            'database.default' => 'testing',
            'vote.models.user' => User::class,
            'vote.uuids' => true,
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [VoteServiceProvider::class];
    }
}
