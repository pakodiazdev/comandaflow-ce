<?php

namespace CF\CE\TimeTracker\Commands;

use CF\CE\TimeTracker\Services\GitHubService;
use CF\CE\TimeTracker\Services\TaskStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class TaskUploadCommand extends Command
{
    protected $signature = 'task:upload {issue : The GitHub issue number}';
    protected $description = 'Upload/sync local task file to GitHub issue without changing status';

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

        $this->info("Uploading task file to GitHub issue #{$issueNumber}...");

        try {
            // Find local task file
            $taskPath = $this->taskStorage->findTaskFile($issueNumber);
            
            if (!$taskPath) {
                $this->error("Task file not found for issue #{$issueNumber}. No local file to upload.");
                return self::FAILURE;
            }

            $this->info("Found local task file: {$taskPath}");
            $content = $this->taskStorage->loadTask($taskPath);

            if (!$content) {
                $this->error("Failed to load task content from local file.");
                return self::FAILURE;
            }

            $this->info("Syncing complete content to GitHub...");
            if (!$this->githubService->updateIssueWithFullContent($issueNumber, $content)) {
                $this->error("Failed to upload content to GitHub issue. Please check your credentials and connection.");
                return self::FAILURE;
            }

            $this->info("✅ Successfully uploaded local content to GitHub issue #{$issueNumber}");
            $this->line("Local file: {$taskPath}");
            $this->line("Status: Content synchronized (no status change)");

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}