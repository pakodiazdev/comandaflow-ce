<?php

namespace CF\CE\TimeTracker\Commands;

use CF\CE\TimeTracker\Services\GitHubService;
use CF\CE\TimeTracker\Services\TaskStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class TaskStopCommand extends Command
{
    protected $signature = 'task:stop {issue : The GitHub issue number}';
    protected $description = 'Stop/pause time tracking for a GitHub issue (status: waiting)';

    private GitHubService $githubService;
    private TaskStorageService $taskStorage;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->githubService = App::make('cf.time-tracker.github');
        $this->taskStorage = App::make('cf.time-tracker.task-storage');

        $issueNumber = (int) $this->argument('issue');

        if ($issueNumber <= 0) {
            $this->error('Invalid issue number. Please provide a valid GitHub issue number.');
            return self::FAILURE;
        }

        $this->info("Stopping/pausing time tracking for issue #{$issueNumber}...");

        try {
            // Find task file
            $taskPath = $this->taskStorage->findTaskFile($issueNumber);
            
            if (!$taskPath) {
                $this->error("Task file not found for issue #{$issueNumber}. Please start the task first.");
                return self::FAILURE;
            }

            $this->info("Found task file: {$taskPath}");
            $content = $this->taskStorage->loadTask($taskPath);

            if (!$content) {
                $this->error("Failed to load task content.");
                return self::FAILURE;
            }

            $sessions = $this->taskStorage->extractTimeSection($content);

            $hasActiveSession = false;
            foreach ($sessions as $session) {
                if ($session['end'] === 'HH:MM') {
                    $hasActiveSession = true;
                    break;
                }
            }

            if (!$hasActiveSession) {
                $this->warn("No active session found for issue #{$issueNumber}. Task is already stopped or not started.");
                return self::FAILURE;
            }

            // End the current session (stop/pause)
            $timezone = config('app.time_tracker_timezone', 'UTC');
            $stopTime = \Carbon\Carbon::now($timezone);
            $sessions = $this->taskStorage->addOrUpdateSession($sessions, 'end', $stopTime);
            $trackedTimeMinutes = $this->taskStorage->calculateTrackedTimeInMinutes($sessions);
            $trackedTime = $this->taskStorage->calculateTrackedTime($sessions);
            
            $updatedContent = $this->taskStorage->updateTimeSection($content, $sessions, $trackedTime);

            if (!$this->taskStorage->saveTask($taskPath, $updatedContent)) {
                $this->error("Failed to save task file locally.");
                return self::FAILURE;
            }

            $this->info("Updating GitHub issue with waiting status...");
            if (!$this->githubService->updateIssueWithFullContentAndStatus($issueNumber, $updatedContent, 'waiting')) {
                $this->warn("Task stopped locally but failed to update GitHub issue. You may need to sync manually.");
            } else {
                $this->info("Successfully updated GitHub issue with waiting status.");
            }

            $currentTime = $stopTime->format('H:i');
            $this->info("⏸️  Time tracking paused for issue #{$issueNumber} at {$currentTime}");
            $this->line("Status: Waiting (can be resumed with task:start)");
            $this->line("Total tracked time: {$trackedTime}");
            $this->line("Task saved to: {$taskPath}");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}