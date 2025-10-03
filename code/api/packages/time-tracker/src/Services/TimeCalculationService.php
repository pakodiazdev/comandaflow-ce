<?php

namespace CF\CE\TimeTracker\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TimeCalculationService
{
    private string $timezone;

    public function __construct(string $timezone = null)
    {
        $this->timezone = $timezone ?? config('app.time_tracker_timezone', 'UTC');
    }

    /**
     * Parse sessions from task content
     */
    public function parseSessionsFromContent(string $content): array
    {
        // Parse JSON sessions from markdown code block
        $jsonPattern = '/```json\s*\n?\s*(\[[\s\S]*?\])\s*\n?```/';
        
        if (preg_match($jsonPattern, $content, $jsonMatches)) {
            try {
                $sessions = json_decode($jsonMatches[1], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error("TimeCalculationService: JSON decode error", [
                        'error' => json_last_error_msg(),
                        'json' => $jsonMatches[1]
                    ]);
                    return [];
                }
                
                if (is_array($sessions) && !empty($sessions)) {
                    Log::info("TimeCalculationService: Parsed sessions from JSON", [
                        'sessions_count' => count($sessions)
                    ]);
                    return $sessions;
                }
            } catch (\Exception $e) {
                Log::error("TimeCalculationService: Failed to parse JSON sessions", [
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        }

        Log::info("TimeCalculationService: No JSON sessions found in content");
        return [];
    }

    /**
     * Calculate total tracked time in minutes from sessions
     */
    public function calculateTotalMinutes(array $sessions): int
    {
        $totalMinutes = 0;

        Log::info("TimeCalculationService: Calculating total minutes", [
            'sessions_count' => count($sessions),
            'timezone' => $this->timezone
        ]);

        foreach ($sessions as $index => $session) {
            if (!isset($session['start'], $session['end'], $session['date'])) {
                Log::warning("TimeCalculationService: Skipping invalid session", [
                    'session_index' => $index,
                    'session' => $session
                ]);
                continue;
            }

            // Skip active sessions (end = HH:MM) or placeholder sessions
            if ($session['start'] === 'HH:MM' || $session['end'] === 'HH:MM') {
                Log::info("TimeCalculationService: Skipping active session", [
                    'session_index' => $index,
                    'session' => $session
                ]);
                continue;
            }

            try {
                $startTime = Carbon::createFromFormat('Y-m-d H:i', $session['date'] . ' ' . $session['start'], $this->timezone);
                $endTime = Carbon::createFromFormat('Y-m-d H:i', $session['date'] . ' ' . $session['end'], $this->timezone);

                if ($startTime && $endTime && $endTime > $startTime) {
                    $sessionMinutes = $startTime->diffInMinutes($endTime);
                    $totalMinutes += $sessionMinutes;
                    
                    Log::info("TimeCalculationService: Added session minutes", [
                        'session_index' => $index,
                        'session_minutes' => $sessionMinutes,
                        'start' => $startTime->toDateTimeString(),
                        'end' => $endTime->toDateTimeString()
                    ]);
                } else {
                    Log::warning("TimeCalculationService: Invalid time range", [
                        'session_index' => $index,
                        'session' => $session,
                        'start_time' => $startTime ? $startTime->toDateTimeString() : 'invalid',
                        'end_time' => $endTime ? $endTime->toDateTimeString() : 'invalid'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("TimeCalculationService: Error parsing session time", [
                    'session_index' => $index,
                    'session' => $session,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("TimeCalculationService: Total calculation complete", [
            'total_minutes' => $totalMinutes
        ]);

        return $totalMinutes;
    }

    /**
     * Format minutes to human readable duration
     */
    public function formatDuration(int $minutes): string
    {
        if ($minutes === 0) {
            return '0m';
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

    /**
     * Recalculate and update tracked time in task content
     */
    public function recalculateTrackedTime(string $content): array
    {
        Log::info("TimeCalculationService: Starting recalculation");

        // Parse sessions from content
        $sessions = $this->parseSessionsFromContent($content);
        
        // Calculate total time
        $totalMinutes = $this->calculateTotalMinutes($sessions);
        $formattedTime = $this->formatDuration($totalMinutes);

        // Update the tracked time in content
        $updatedContent = $this->updateTrackedTimeInContent($content, $formattedTime);

        Log::info("TimeCalculationService: Recalculation complete", [
            'sessions_count' => count($sessions),
            'total_minutes' => $totalMinutes,
            'formatted_time' => $formattedTime
        ]);

        return [
            'content' => $updatedContent,
            'sessions' => $sessions,
            'total_minutes' => $totalMinutes,
            'formatted_time' => $formattedTime
        ];
    }

    /**
     * Update tracked time in content
     */
    private function updateTrackedTimeInContent(string $content, string $formattedTime): string
    {
        // Pattern to find "- **Tracked:** `XXX`" in the Time section
        $trackedPattern = '/(-\s*\*\*Tracked:\*\*\s*`)[^`]*(`)/';
        
        if (preg_match($trackedPattern, $content)) {
            Log::info("TimeCalculationService: Updating Tracked field", [
                'formatted_time' => $formattedTime
            ]);
            // Use callback to avoid backreference issues
            return preg_replace_callback($trackedPattern, function($matches) use ($formattedTime) {
                return $matches[1] . $formattedTime . $matches[2];
            }, $content);
        }

        Log::info("TimeCalculationService: Tracked field not found, will add after JSON sessions");

        // If not found, try to add it after the JSON sessions block
        $jsonPattern = '/(```json\s*\n?\s*\[[\s\S]*?\]\s*\n?```)/';
        
        if (preg_match($jsonPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $insertPosition = $matches[0][1] + strlen($matches[0][0]);
            $beforeInsert = substr($content, 0, $insertPosition);
            $afterInsert = substr($content, $insertPosition);
            
            return $beforeInsert . "\n\n**Total tracked:** " . $formattedTime . $afterInsert;
        }

        // If no JSON block found, append at the end
        return $content . "\n\n**Total tracked:** " . $formattedTime;
    }

    /**
     * Validate session data integrity
     */
    public function validateSessions(array $sessions): array
    {
        $validationResults = [
            'valid_sessions' => [],
            'invalid_sessions' => [],
            'warnings' => []
        ];

        foreach ($sessions as $index => $session) {
            $sessionValidation = $this->validateSession($session, $index);
            
            if ($sessionValidation['valid']) {
                $validationResults['valid_sessions'][] = $session;
            } else {
                $validationResults['invalid_sessions'][] = [
                    'session' => $session,
                    'errors' => $sessionValidation['errors']
                ];
            }

            if (!empty($sessionValidation['warnings'])) {
                $validationResults['warnings'] = array_merge(
                    $validationResults['warnings'], 
                    $sessionValidation['warnings']
                );
            }
        }

        return $validationResults;
    }

    /**
     * Validate individual session
     */
    private function validateSession(array $session, int $index): array
    {
        $result = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];

        // Check required fields
        if (!isset($session['date'], $session['start'], $session['end'])) {
            $result['valid'] = false;
            $result['errors'][] = "Session {$index}: Missing required fields (date, start, end)";
            return $result;
        }

        // Skip validation for active sessions
        if ($session['start'] === 'HH:MM' || $session['end'] === 'HH:MM') {
            $result['warnings'][] = "Session {$index}: Active session (contains HH:MM)";
            return $result;
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $session['date'])) {
            $result['valid'] = false;
            $result['errors'][] = "Session {$index}: Invalid date format '{$session['date']}' (expected YYYY-MM-DD)";
        }

        // Validate time format
        if (!preg_match('/^\d{2}:\d{2}$/', $session['start'])) {
            $result['valid'] = false;
            $result['errors'][] = "Session {$index}: Invalid start time format '{$session['start']}' (expected HH:MM)";
        }

        if (!preg_match('/^\d{2}:\d{2}$/', $session['end'])) {
            $result['valid'] = false;
            $result['errors'][] = "Session {$index}: Invalid end time format '{$session['end']}' (expected HH:MM)";
        }

        // Validate time logic (end > start)
        if ($result['valid']) {
            try {
                $startTime = Carbon::createFromFormat('Y-m-d H:i', $session['date'] . ' ' . $session['start'], $this->timezone);
                $endTime = Carbon::createFromFormat('Y-m-d H:i', $session['date'] . ' ' . $session['end'], $this->timezone);

                if ($endTime <= $startTime) {
                    $result['valid'] = false;
                    $result['errors'][] = "Session {$index}: End time must be after start time";
                }
            } catch (\Exception $e) {
                $result['valid'] = false;
                $result['errors'][] = "Session {$index}: Error parsing times - " . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Get timezone being used for calculations
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * Set timezone for calculations
     */
    public function setTimezone(string $timezone): void
    {
        $this->timezone = $timezone;
    }
}