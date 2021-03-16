<?php

declare(strict_types=1);

namespace LaravelInteraction\Vote;

use Illuminate\Support\ServiceProvider;

class VoteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    $this->getConfigPath() => config_path('vote.php'),
                ],
                'vote-config'
            );
            $this->publishes(
                [
                    $this->getMigrationsPath() => database_path('migrations'),
                ],
                'vote-migrations'
            );
            if ($this->shouldLoadMigrations()) {
                $this->loadMigrationsFrom($this->getMigrationsPath());
            }
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->getConfigPath(), 'vote');
    }

    protected function getConfigPath(): string
    {
        return __DIR__ . '/../config/vote.php';
    }

    protected function getMigrationsPath(): string
    {
        return __DIR__ . '/../migrations';
    }

    private function shouldLoadMigrations(): bool
    {
        return (bool) config('vote.load_migrations');
    }
}
