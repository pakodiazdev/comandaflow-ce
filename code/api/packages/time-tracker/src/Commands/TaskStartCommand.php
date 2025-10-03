<?php

namespace CF\CE\TimeTracker\Commands;

use CF\CE\TimeTracker\Services\GitHubService;
use CF\CE\TimeTracker\Services\TaskStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class TaskStartCommand extends Command
{
    protected $signature = 'task:start {issue : The GitHub issue number}';
    protected $description = 'Start time tracking for a GitHub issue';

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

        $this->info("Starting time tracking for issue #{$issueNumber}...");

        try {
            $existingTaskPath = $this->taskStorage->findTaskFile($issueNumber);
            
            if ($existingTaskPath) {
                $this->info("Found existing task file: {$existingTaskPath}");
                $content = $this->taskStorage->loadTask($existingTaskPath);
                $taskPath = $existingTaskPath;
            } else {
                $this->info("Fetching issue from GitHub...");
                $issue = $this->githubService->fetchIssue($issueNumber);
                
                if (!$issue) {
                    $this->error("Failed to fetch issue #{$issueNumber} from GitHub. Please check your credentials and issue number.");
                    return self::FAILURE;
                }

                // Create task file
                $filename = $this->taskStorage->generateFilename($issueNumber, $issue['title']);
                $taskPath = $this->taskStorage->getTaskPath($filename);
                $content = $issue['body'] ?? '';

                $this->info("Creating new task file: {$taskPath}");
            }

            $sessions = $this->taskStorage->extractTimeSection($content);

            $hasActiveSession = false;
            foreach ($sessions as $session) {
                if ($session['end'] === 'HH:MM') {
                    $hasActiveSession = true;
                    break;
                }
            }

            if ($hasActiveSession) {
                $this->warn("There's already an active session for this task. Please end the current session first.");
                return self::FAILURE;
            }

            $sessions = $this->taskStorage->addOrUpdateSession($sessions, 'start');
            $trackedTimeMinutes = $this->taskStorage->calculateTrackedTimeInMinutes($sessions);
            $trackedTime = $this->taskStorage->calculateTrackedTime($sessions);

            $updatedContent = $this->taskStorage->updateTimeSection($content, $sessions, $trackedTime);

            if (!$this->taskStorage->saveTask($taskPath, $updatedContent)) {
                $this->error("Failed to save task file locally.");
                return self::FAILURE;
            }

            $this->info("Updating GitHub issue with in-progress status...");
            if (!$this->githubService->updateIssueWithFullContentAndStatus($issueNumber, $updatedContent, 'in-progress')) {
                $this->warn("Task started locally but failed to update GitHub issue. You may need to sync manually.");
            } else {
                $this->info("Successfully updated GitHub issue with in-progress status.");
            }

            $timezone = config('app.time_tracker_timezone', 'UTC');
            $currentTime = \Carbon\Carbon::now($timezone)->format('H:i');
            $this->info("✅ Time tracking started for issue #{$issueNumber} at {$currentTime}");
            $this->line("Task saved to: {$taskPath}");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}