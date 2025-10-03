<?php

namespace CF\CE\TimeTracker\Commands;

use CF\CE\TimeTracker\Services\GitHubService;
use CF\CE\TimeTracker\Services\TaskStorageService;
use CF\CE\TimeTracker\Services\TimeCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TaskRecalculateCommand extends Command
{
    protected $signature = 'task:recalculate {issue? : The GitHub issue number (optional - will recalculate all if not provided)} 
                                              {--validate : Validate session data integrity}
                                              {--no-sync : Skip syncing results back to GitHub}
                                              {--dry-run : Show what would be changed without making changes}';
    
    protected $description = 'Recalculate tracked time for GitHub issues and sync to GitHub. Local files first, fetch from GitHub if not found.';

    private GitHubService $githubService;
    private TaskStorageService $taskStorage;
    private TimeCalculationService $timeCalculation;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->githubService = App::make('cf.time-tracker.github');
        $this->taskStorage = App::make('cf.time-tracker.task-storage');
        $this->timeCalculation = new TimeCalculationService();

        $issueNumber = $this->argument('issue');
        $validateSessions = $this->option('validate');
        $syncToGitHub = !$this->option('no-sync'); // Sync by default unless --no-sync is passed
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        }

        $this->info('📁 LOCAL FIRST MODE - Will use local files when available, GitHub as fallback');

        try {
            if ($issueNumber) {
                return $this->recalculateSingleIssue((int)$issueNumber, $validateSessions, $syncToGitHub, $dryRun);
            } else {
                return $this->recalculateAllIssues($validateSessions, $syncToGitHub, $dryRun);
            }
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
            Log::error("TaskRecalculateCommand error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }

    private function recalculateSingleIssue(int $issueNumber, bool $validate, bool $sync, bool $dryRun): int
    {
        $this->info("🔄 Recalculating time for issue #{$issueNumber}...");

        $taskPath = $this->taskStorage->findTaskFile($issueNumber);
        
        if ($taskPath) {
            $this->line("📁 Using existing local task file: {$taskPath}");
            $content = $this->taskStorage->loadTask($taskPath);
            
            if (!$content) {
                $this->error("Failed to load task content from {$taskPath}");
                return self::FAILURE;
            }
        } else {
            $this->info("📁 No local file found, fetching from GitHub...");
            
            $issue = $this->githubService->fetchIssue($issueNumber);
            
            if (!$issue) {
                $this->error("❌ Failed to fetch from GitHub and no local file found for issue #{$issueNumber}");
                return self::FAILURE;
            }

            $this->info("✅ Successfully fetched issue from GitHub");
            $this->line("📝 Title: {$issue['title']}");

            $content = $issue['body'] ?? '';
            $taskPath = $this->determineTaskPath($issueNumber, $issue['title'], $content);

            $this->line("📁 Will create new task file from GitHub content");
        }

        return $this->processTask($issueNumber, $taskPath, $content, $validate, $sync, $dryRun);
    }

    private function recalculateAllIssues(bool $validate, bool $sync, bool $dryRun): int
    {
        $this->info("🔄 Recalculating time for all issues...");

        $taskFiles = $this->taskStorage->getAllTaskFiles();
        
        if (empty($taskFiles)) {
            $this->warn("No task files found.");
            return self::SUCCESS;
        }

        $this->line("📁 Found " . count($taskFiles) . " task files");
        $this->info("📁 Using local-first mode for batch processing");

        $successCount = 0;
        $errorCount = 0;

        foreach ($taskFiles as $taskPath) {
            $issueNumber = $this->extractIssueNumberFromPath($taskPath);
            
            if (!$issueNumber) {
                $this->warn("⚠️  Could not extract issue number from: {$taskPath}");
                $errorCount++;
                continue;
            }

            $this->line("📝 Processing issue #{$issueNumber}...");

            $result = $this->recalculateSingleIssue($issueNumber, $validate, $sync, $dryRun);
            
            if ($result === self::SUCCESS) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        $this->info("✅ Completed recalculation:");
        $this->line("   🟢 Successful: {$successCount}");
        $this->line("   🔴 Errors: {$errorCount}");

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function processTask(int $issueNumber, string $taskPath, string $content, bool $validate, bool $sync, bool $dryRun, bool $verbose = true): int
    {
        $result = $this->timeCalculation->recalculateTrackedTime($content);
        
        if ($verbose) {
            $this->line("📊 Sessions found: " . count($result['sessions']));
            $this->line("⏱️  Current total: {$result['formatted_time']} ({$result['total_minutes']} minutes)");
        }

        if ($validate) {
            $validation = $this->timeCalculation->validateSessions($result['sessions']);
            
            if (!empty($validation['invalid_sessions'])) {
                $this->warn("⚠️  Found " . count($validation['invalid_sessions']) . " invalid sessions:");
                foreach ($validation['invalid_sessions'] as $invalid) {
                    $this->error("   ❌ " . implode(', ', $invalid['errors']));
                }
            }

            if (!empty($validation['warnings'])) {
                foreach ($validation['warnings'] as $warning) {
                    $this->warn("   ⚠️  {$warning}");
                }
            }

            if (empty($validation['invalid_sessions']) && $verbose) {
                $this->info("✅ All sessions are valid");
            }
        }

        $contentChanged = $result['content'] !== $content;
        
        if (!$contentChanged && $verbose) {
            $this->info("ℹ️  No changes needed - tracked time already correct");
            return self::SUCCESS;
        }

        if ($dryRun) {
            if ($verbose) {
                $this->warn("🔍 DRY RUN: Would update tracked time to {$result['formatted_time']}");
            }
            return self::SUCCESS;
        }

        if (!$this->taskStorage->saveTask($taskPath, $result['content'])) {
            $this->error("❌ Failed to save updated content to {$taskPath}");
            return self::FAILURE;
        }

        if ($verbose) {
            $this->info("✅ Updated local task file");
        }

        if ($sync) {
            if ($verbose) {
                $this->line("🔄 Syncing to GitHub...");
            }

            $status = $this->determineTaskStatus($result['sessions']);
            
            if (!$this->githubService->updateIssueWithFullContentAndStatus($issueNumber, $result['content'], $status)) {
                $this->warn("⚠️  Local file updated but failed to sync to GitHub");
                return self::FAILURE;
            }

            if ($verbose) {
                $this->info("✅ Successfully synced to GitHub");
            }
        }

        if ($verbose) {
            $this->info("✅ Issue #{$issueNumber} recalculation complete");
        }

        return self::SUCCESS;
    }

    private function determineTaskStatus(array $sessions): string
    {
        foreach ($sessions as $session) {
            if ($session['end'] === 'HH:MM') {
                return 'in-progress';
            }
        }

        return empty($sessions) ? 'none' : 'waiting';
    }

    private function extractIssueNumberFromPath(string $path): ?int
    {
        $filename = basename($path);
        
        if (preg_match('/^(\d+)\s*-/', $filename, $matches)) {
            return (int)$matches[1];
        }

        return null;
    }

    private function determineTaskPath(int $issueNumber, string $title, string $content): string
    {
        $sessions = $this->timeCalculation->parseSessionsFromContent($content);
        
        $targetDate = null;
        
        if (!empty($sessions)) {
            $dates = array_map(fn($session) => $session['date'], $sessions);
            $targetDate = min($dates);
            $this->line("📅 Using first session date for file placement: {$targetDate}");
        } else {
            $timezone = config('app.time_tracker_timezone', 'UTC');
            $targetDate = Carbon::now($timezone)->format('Y-m-d');
            $this->line("📅 No sessions found, using current date: {$targetDate}");
        }

        $dateCarbon = Carbon::createFromFormat('Y-m-d', $targetDate);
        $year = $dateCarbon->format('Y');
        $month = $dateCarbon->format('m');
        
        $filename = $this->taskStorage->generateFilename($issueNumber, $title);
        
        $basePath = rtrim(config('time-tracker.task_path', env('PATH_TASK', '/tasks')), '/');
        
        return "{$basePath}/{$year}/{$month}/{$filename}";
    }
}
