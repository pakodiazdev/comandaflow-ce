<?php

namespace CF\CE\TimeTracker\Commands;

use CF\CE\TimeTracker\Services\GitHubService;
use CF\CE\TimeTracker\Services\TaskStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class TaskEndCommand extends Command
{
    protected $signature = 'task:end {issue : The GitHub issue number} {closure-time=0 : Additional minutes for task closure and repository upload}';
    protected $description = 'End time tracking for a GitHub issue with optional closure time
    
Examples:
  task:end 123              # End task immediately (default 0min closure time)
  task:end 123 15           # End task with 15min closure time
  task:end 123 0            # End task immediately without closure time';

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
        $closureTimeMinutes = (int) $this->argument('closure-time');

        if ($issueNumber <= 0) {
            $this->error('Invalid issue number. Please provide a valid GitHub issue number.');
            return self::FAILURE;
        }

        if ($closureTimeMinutes < 0 || $closureTimeMinutes > 120) {
            $this->error('Closure time must be between 0 and 120 minutes.');
            return self::FAILURE;
        }

        $this->info("Ending time tracking for issue #{$issueNumber} (adding {$closureTimeMinutes}min for closure)...");

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

            // Check if task is in "waiting" state (no active session but has previous sessions)
            $isWaitingState = !$hasActiveSession && !empty($sessions);

            if (!$hasActiveSession && !$isWaitingState) {
                $this->warn("No active session found for issue #{$issueNumber}. Please start a session first.");
                return self::FAILURE;
            }

            if ($isWaitingState) {
                // Task is in waiting state - just change status to completed without adding session
                $this->info("Task is in waiting state. Marking as completed without adding new session.");
                
                // Calculate current tracked time from existing sessions
                $trackedTimeMinutes = $this->taskStorage->calculateTrackedTimeInMinutes($sessions);
                $trackedTime = $this->taskStorage->calculateTrackedTime($sessions);
                
                // Update content but don't modify sessions or add closure time
                $updatedContent = $this->taskStorage->updateTimeSection($content, $sessions, $trackedTime);
                
                if (!$this->taskStorage->saveTask($taskPath, $updatedContent)) {
                    $this->error("Failed to save task file locally.");
                    return self::FAILURE;
                }

                $this->info("Updating GitHub issue with completed status...");
                if (!$this->githubService->updateIssueWithFullContentAndStatus($issueNumber, $updatedContent, 'completed')) {
                    $this->warn("Task marked as completed locally but failed to update GitHub issue. You may need to sync manually.");
                } else {
                    $this->info("Successfully updated GitHub issue with completed status.");
                }

                $timezone = config('app.time_tracker_timezone', 'UTC');
                $currentTime = \Carbon\Carbon::now($timezone)->format('H:i');
                $this->info("✅ Task #{$issueNumber} marked as completed at {$currentTime}");
                $this->line("Status changed: Waiting → Completed");
                $this->line("Total tracked time: {$trackedTime} (no additional time added)");
                $this->line("Task saved to: {$taskPath}");

                return self::SUCCESS;
            }

            // Task has active session - normal end flow with closure time
            $timezone = config('app.time_tracker_timezone', 'UTC');
            $endTime = \Carbon\Carbon::now($timezone)->addMinutes($closureTimeMinutes);
            $sessions = $this->taskStorage->addOrUpdateSession($sessions, 'end', $endTime);
            $trackedTimeMinutes = $this->taskStorage->calculateTrackedTimeInMinutes($sessions);
            $trackedTime = $this->taskStorage->calculateTrackedTime($sessions);
            $updatedContent = $this->taskStorage->updateTimeSection($content, $sessions, $trackedTime);
            if (!$this->taskStorage->saveTask($taskPath, $updatedContent)) {
                $this->error("Failed to save task file locally.");
                return self::FAILURE;
            }

            $this->info("Updating GitHub issue with completed status...");
            if (!$this->githubService->updateIssueWithFullContentAndStatus($issueNumber, $updatedContent, 'completed')) {
                $this->warn("Task ended locally but failed to update GitHub issue. You may need to sync manually.");
            } else {
                $this->info("Successfully updated GitHub issue with completed status.");
            }

            $timezone = config('app.time_tracker_timezone', 'UTC');
            $currentTime = \Carbon\Carbon::now($timezone)->format('H:i');
            $endTimeWithClosure = \Carbon\Carbon::now($timezone)->addMinutes($closureTimeMinutes)->format('H:i');
            $this->info("✅ Time tracking ended for issue #{$issueNumber} at {$currentTime} (projected end: {$endTimeWithClosure})");
            $this->line("Closure time added: {$closureTimeMinutes} minutes");
            $this->line("Total tracked time: {$trackedTime}");
            $this->line("Task saved to: {$taskPath}");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}