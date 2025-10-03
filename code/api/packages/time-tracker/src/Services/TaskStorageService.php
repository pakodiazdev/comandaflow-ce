<?php

namespace CF\CE\TimeTracker\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TaskStorageService
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Get current time in the configured time tracker timezone
     */
    private function nowInTimeTrackerTimezone(): Carbon
    {
        $timezone = config('app.time_tracker_timezone', 'UTC');
        return Carbon::now($timezone);
    }

    public function generateFilename(int $issueNumber, string $title): string
    {
        $slug = Str::slug($title);
        return "{$issueNumber} - {$slug}.md";
    }

    public function getTaskPath(string $filename, ?Carbon $date = null): string
    {
        $date = $date ?? $this->nowInTimeTrackerTimezone();
        $year = $date->format('Y');
        $month = $date->format('m');
        
        return "{$this->basePath}/{$year}/{$month}/{$filename}";
    }

    public function findTaskFile(int $issueNumber): ?string
    {
        $pattern = "{$issueNumber} - *.md";
        $searchPaths = [
            "{$this->basePath}/*/*/*",
        ];

        foreach ($searchPaths as $searchPath) {
            $files = glob($searchPath);
            foreach ($files as $file) {
                if (fnmatch("*/{$pattern}", $file)) {
                    return $file;
                }
            }
        }

        return null;
    }

    public function saveTask(string $filePath, string $content): bool
    {
        $directory = dirname($filePath);
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return File::put($filePath, $content) !== false;
    }

    public function loadTask(string $filePath): ?string
    {
        if (!File::exists($filePath)) {
            return null;
        }

        return File::get($filePath);
    }

    public function updateTimeSection(string $content, array $sessions, string $trackedTime): string
    {
        $estimates = $this->extractEstimates($content);
        $timeSection = $this->buildTimeSection($sessions, $trackedTime, $estimates);
        $pattern = '/## ⏱️ Time.*?(?=\n## [^⏱️]|\z)/s';
        
        if (preg_match($pattern, $content)) {
            return preg_replace($pattern, $timeSection, $content);
        } else {
            return $content . "\n\n" . $timeSection;
        }
    }

    public function extractTimeSection(string $content): array
    {
        $pattern = '/## ⏱️ Time.*?### 📅 Sessions\s*```json\s*(.*?)\s*```/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $jsonString = trim($matches[1]);
            $sessions = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($sessions)) {
                return $sessions;
            }
        }

        return [
            ['date' => $this->nowInTimeTrackerTimezone()->format('Y-m-d'), 'start' => 'HH:MM', 'end' => 'HH:MM']
        ];
    }

    /**
     * Extract existing estimates from markdown content
     */
    public function extractEstimates(string $content): array
    {
        $optimistic = '—';
        $pessimistic = '—';
        
        // Extract Optimistic estimate
        if (preg_match('/- \*\*Optimistic:\*\* `([^`]+)`/', $content, $matches)) {
            $optimistic = $matches[1];
        }
        
        // Extract Pessimistic estimate
        if (preg_match('/- \*\*Pessimistic:\*\* `([^`]+)`/', $content, $matches)) {
            $pessimistic = $matches[1];
        }
        
        return [
            'optimistic' => $optimistic,
            'pessimistic' => $pessimistic
        ];
    }

    private function buildTimeSection(array $sessions, string $trackedTime, ?array $estimates = null): string
    {
        $estimates = $estimates ?? ['optimistic' => '—', 'pessimistic' => '—'];
        
        $formattedSessions = [];
        foreach ($sessions as $session) {
            $sessionJson = json_encode($session, JSON_UNESCAPED_UNICODE);
            $sessionJson = str_replace('":"', '": "', $sessionJson);
            $sessionJson = str_replace('","', '", "', $sessionJson);
            $formattedSessions[] = '    ' . $sessionJson;
        }
        $sessionsJson = "[\n" . implode(",\n", $formattedSessions) . "\n]";
        
        return "## ⏱️ Time\n" .
               "### 📊 Estimates\n" .
               "- **Optimistic:** `{$estimates['optimistic']}`\n" .
               "- **Pessimistic:** `{$estimates['pessimistic']}`\n" .
               "- **Tracked:** `{$trackedTime}`\n\n" .
               "### 📅 Sessions\n" .
               "```json\n{$sessionsJson}\n```";
    }

    public function calculateTrackedTime(array $sessions): string
    {
        $totalMinutes = $this->calculateTrackedTimeInMinutes($sessions);
        return $this->formatDuration($totalMinutes);
    }

    /**
     * Calculate tracked time in minutes as integer
     */
    public function calculateTrackedTimeInMinutes(array $sessions): int
    {
        $totalMinutes = 0;

        foreach ($sessions as $session) {
            if (!isset($session['start'], $session['end'], $session['date'])) {
                continue;
            }

            if ($session['start'] === 'HH:MM' || $session['end'] === 'HH:MM') {
                continue;
            }

            $timezone = config('app.time_tracker_timezone', 'UTC');
            $startTime = Carbon::createFromFormat('Y-m-d H:i', $session['date'] . ' ' . $session['start'], $timezone);
            $endTime = Carbon::createFromFormat('Y-m-d H:i', $session['date'] . ' ' . $session['end'], $timezone);

            if ($startTime && $endTime && $endTime > $startTime) {
                $totalMinutes += $startTime->diffInMinutes($endTime);
            }
        }

        return $totalMinutes;
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes === 0) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours === 0) {
            return "{$remainingMinutes}m";
        }

        if ($remainingMinutes === 0) {
            return "{$hours}h";
        }

        return "{$hours}h {$remainingMinutes}m";
    }

    public function addOrUpdateSession(array $sessions, string $action, ?Carbon $time = null): array
    {
        $time = $time ?? $this->nowInTimeTrackerTimezone();
        $dateStr = $time->format('Y-m-d');
        $timeStr = $time->format('H:i');

        if ($action === 'start') {
            $sessions = array_filter($sessions, function ($session) {
                return !($session['start'] === 'HH:MM' && $session['end'] === 'HH:MM');
            });

            $sessions[] = [
                'date' => $dateStr,
                'start' => $timeStr,
                'end' => 'HH:MM',
            ];
        } elseif ($action === 'end') {
            foreach ($sessions as &$session) {
                if ($session['end'] === 'HH:MM') {
                    if ($session['date'] === $dateStr) {
                        $session['end'] = $timeStr;
                    } else {
                        $session['end'] = '23:59';
                        
                        $sessions[] = [
                            'date' => $dateStr,
                            'start' => '00:00',
                            'end' => $timeStr,
                        ];
                    }
                    break;
                }
            }
        }

        return array_values($sessions);
    }

    /**
     * Extract estimated time from task content
     */
    public function extractEstimatedTime(string $content): int
    {
        // Look for patterns like "Estimated Time: 60 minutes" or "Estimate: 2h 30m"
        $patterns = [
            '/(?:estimated?\s*time|estimate):\*?\*?\s*(\d+)\s*(?:minutes?|mins?|m)/i',
            '/(?:estimated?\s*time|estimate):\*?\*?\s*(\d+)\s*(?:hours?|hrs?|h)\s*(\d+)?\s*(?:minutes?|mins?|m)?/i',
            '/(?:estimated?\s*time|estimate):\*?\*?\s*(\d+)h/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                if (count($matches) >= 3 && !empty($matches[2])) {
                    // Format: "2h 30m"
                    return (int)$matches[1] * 60 + (int)$matches[2];
                } else {
                    // Format: "60 minutes" or "2h"
                    $value = (int)$matches[1];
                    if (strpos($pattern, 'hours?|hrs?|h') !== false && !strpos($matches[0], 'minute')) {
                        return $value * 60; // Convert hours to minutes
                    }
                    return $value; // Already in minutes
                }
            }
        }

        return 0; // No estimated time found
    }

    /**
     * Get all task files recursively
     */
    public function getAllTaskFiles(): array
    {
        $taskFiles = [];
        
        if (!File::exists($this->basePath)) {
            return $taskFiles;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->basePath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'md') {
                $taskFiles[] = $file->getPathname();
            }
        }

        return $taskFiles;
    }
}