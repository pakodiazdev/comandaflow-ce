<?php

namespace CF\CE\TimeTracker;

use CF\CE\TimeTracker\Commands\TaskEndCommand;
use CF\CE\TimeTracker\Commands\TaskRecalculateCommand;
use CF\CE\TimeTracker\Commands\TaskStartCommand;
use CF\CE\TimeTracker\Commands\TaskStopCommand;
use CF\CE\TimeTracker\Commands\TaskUploadCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class TimeTrackerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('cf.time-tracker.github', function ($app) {
            $configToken = config('time-tracker.github.token');
            $envToken = env('GITHUB_TOKEN');
            $configRepo = config('time-tracker.github.repo');
            $envRepo = env('GITHUB_REPO');
            
            $token = $configToken ?: $envToken;
            $repo = $configRepo ?: $envRepo;
            
            if (!$token) {
                throw new \InvalidArgumentException('GitHub token is required but not provided. Please set GITHUB_TOKEN environment variable.');
            }
            
            if (!$repo) {
                throw new \InvalidArgumentException('GitHub repository is required but not provided. Please set GITHUB_REPO environment variable.');
            }
            
            return new Services\GitHubService($token, $repo);
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