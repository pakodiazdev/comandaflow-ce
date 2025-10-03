<?php

namespace CF\CE\TimeTracker;

use CF\CE\TimeTracker\Commands\TaskEndCommand;
use CF\CE\TimeTracker\Commands\TaskRecalculateCommand;
use CF\CE\TimeTracker\Commands\TaskStartCommand;
use CF\CE\TimeTracker\Commands\TaskStopCommand;
use CF\CE\TimeTracker\Commands\TaskUploadCommand;
use Illuminate\Support\ServiceProvider;

class TimeTrackerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('cf.time-tracker.github', function ($app) {
            return new Services\GitHubService(
                config('time-tracker.github_token', env('GITHUB_TOKEN', env('PATH_GITHUB'))),
                config('time-tracker.github_repo', env('GITHUB_REPO'))
            );
        });

        $this->app->singleton('cf.time-tracker.task-storage', function ($app) {
            return new Services\TaskStorageService(
                config('time-tracker.task_path', env('PATH_TASK', '/tasks'))
            );
        });

        $this->app->singleton('cf.time-tracker.time-calculation', function ($app) {
            return new Services\TimeCalculationService();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TaskStartCommand::class,
                TaskStopCommand::class,
                TaskEndCommand::class,
                TaskUploadCommand::class,
                TaskRecalculateCommand::class,
            ]);
        }
    }
}