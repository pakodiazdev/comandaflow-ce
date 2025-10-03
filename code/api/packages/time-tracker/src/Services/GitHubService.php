<?php

namespace CF\CE\TimeTracker\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GitHubService
{
    private Client $client;
    private string $token;
    private string $repo;

    public function __construct(string $token, string $repo)
    {
        $this->token = $token;
        $this->repo = $repo;
        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'CF-TimeTracker/1.0',
            ],
        ]);
    }

    /**
     * Fetch issue from GitHub by issue number
     */
    public function fetchIssue(int $issueNumber): ?array
    {
        try {
            $response = $this->client->get("repos/{$this->repo}/issues/{$issueNumber}");
            
            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }
        } catch (GuzzleException $e) {
            Log::error("Failed to fetch GitHub issue #{$issueNumber}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Update issue body on GitHub
     */
    public function updateIssue(int $issueNumber, string $body): bool
    {
        try {
            $response = $this->client->patch("repos/{$this->repo}/issues/{$issueNumber}", [
                'json' => [
                    'body' => $body,
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error("Failed to update GitHub issue #{$issueNumber}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update issue with full content, status indicator, title emoji, GitHub labels and Project status
     */
    public function updateIssueWithFullContentAndStatus(int $issueNumber, string $fullContent, string $status = 'none'): bool
    {
        $issue = $this->fetchIssue($issueNumber);
        if (!$issue) {
            return false;
        }

        $currentTitle = $issue['title'] ?? '';
        $updatedTitle = $this->updateTitleWithStatusEmoji($currentTitle, $status);
        $contentWithStatus = $this->addStatusIndicator($fullContent, $status);
        
        // Update title, body and project status only
        $success = $this->updateIssueWithTitleAndBody($issueNumber, $updatedTitle, $contentWithStatus);
        
        if ($success) {
            // Update GitHub Project status (this is what we actually need)
            $this->updateProjectItemStatus($issueNumber, $status);
        }
        
        return $success;
    }

    /**
     * Update GitHub Project item status using GraphQL API
     */
    private function updateProjectItemStatus(int $issueNumber, string $status): bool
    {
        $projectId = env('GITHUB_PROJECT_ID');
        $statusFieldId = env('GITHUB_PROJECT_STATUS_FIELD_ID');
        
        if (empty($projectId) || empty($statusFieldId)) {
            Log::info("GitHub Project ID or Status Field ID not configured, skipping project status update");
            return true; // Not an error, just not configured
        }

        try {
            // First, get the project item for this issue
            $projectItemId = $this->getProjectItemId($issueNumber);
            
            if (!$projectItemId) {
                Log::warning("Issue #{$issueNumber} not found in project {$projectId}");
                return false;
            }

            // Get the status option ID for the target status
            $statusOptionId = $this->getStatusOptionId($projectId, $statusFieldId, $status);
            
            if (!$statusOptionId) {
                Log::warning("Status option not found for status '{$status}' in project {$projectId}");
                return false;
            }

            // Update the project item status
            return $this->updateProjectItemField($projectItemId, $statusFieldId, $statusOptionId);
            
        } catch (\Exception $e) {
            Log::error("Failed to update GitHub Project status for issue #{$issueNumber}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get project item ID for an issue using GraphQL
     */
    private function getProjectItemId($issueNumber)
    {
        Log::info("🔍 Buscando project item ID para issue #{$issueNumber}");
        
        $query = '
            query($project_number: Int!) {
                viewer {
                    projectV2(number: $project_number) {
                        items(first: 100) {
                            nodes {
                                id
                                content {
                                    ... on Issue {
                                        number
                                        repository {
                                            name
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        ';

        $variables = [
            'project_number' => (int)config('github.project_number')
        ];

        Log::info("🔧 Variables de búsqueda:", $variables);
        Log::info("🏭 Repo esperado: " . config('github.repository'));
        Log::info("🎯 Issue buscado: #{$issueNumber} (tipo: " . gettype($issueNumber) . ")");

        $response = $this->executeGraphQLQuery($query, $variables);
        
        Log::info("🔍 GraphQL response:", ['response' => $response]);
        
        if (!isset($response['data']['viewer']['projectV2']['items'])) {
            Log::warning('No project items found in response', ['response' => $response]);
            return null;
        }

        $items = $response['data']['viewer']['projectV2']['items']['nodes'];
        $repoName = config('github.repository');
        
        Log::info("📋 Total items en proyecto: " . count($items));
        
        foreach ($items as $item) {
            if (isset($item['content']['number']) && isset($item['content']['repository']['name'])) {
                $itemNumber = $item['content']['number'];
                $itemRepo = $item['content']['repository']['name'];
                $itemId = $item['id'];
                
                Log::info("📝 Item encontrado: #{$itemNumber} en {$itemRepo} (ID: {$itemId})");
                
                if ($itemNumber == $issueNumber && $itemRepo === $repoName) {
                    Log::info("✅ ¡MATCH ENCONTRADO! Returning ID: {$itemId}");
                    return $itemId;
                }
            }
        }

        Log::warning("❌ Issue #{$issueNumber} not found in project");
        return null;
    }

    /**
     * Get status option ID for a given status value
     */
    private function getStatusOptionId(string $projectId, string $statusFieldId, string $status): ?string
    {
        // Use direct option IDs from environment configuration
        switch ($status) {
            case 'waiting':
                return env('TIME_TRACKER_WAITING');
            case 'in-progress':
                return env('TIME_TRACKER_IN_PROGRESS');
            case 'completed':
                return env('TIME_TRACKER_COMPLETE');
            default:
                return null;
        }
    }

    /**
     * Update project item field using GraphQL mutation
     */
    private function updateProjectItemField(string $projectItemId, string $fieldId, string $valueId): bool
    {
        Log::info("GitHubService: Starting updateProjectItemField", [
            'projectItemId' => $projectItemId,
            'fieldId' => $fieldId,
            'valueId' => $valueId
        ]);

        $mutation = '
            mutation($itemId: ID!, $projectId: ID!, $fieldId: ID!, $valueId: String!) {
                updateProjectV2ItemFieldValue(input: {
                    projectId: $projectId,
                    itemId: $itemId,
                    fieldId: $fieldId,
                    value: {
                        singleSelectOptionId: $valueId
                    }
                }) {
                    projectV2Item {
                        id
                    }
                }
            }';

        // Necesitamos el project ID para la mutation
        $projectId = env('GITHUB_PROJECT_ID');
        
        $variables = [
            'itemId' => $projectItemId,
            'projectId' => $projectId,
            'fieldId' => $fieldId,
            'valueId' => $valueId
        ];

        Log::info("GitHubService: Sending GraphQL mutation", [
            'variables' => $variables,
            'mutation' => $mutation
        ]);

        $response = $this->executeGraphQLQuery($mutation, $variables);
        
        Log::info("GitHubService: GraphQL response received", [
            'response' => $response
        ]);
        
        $success = isset($response['data']['updateProjectV2ItemFieldValue']['projectV2Item']['id']);
        
        Log::info("GitHubService: updateProjectItemField result", [
            'success' => $success
        ]);
        
        return $success;
    }

    /**
     * Execute GraphQL query against GitHub API
     */
    private function executeGraphQLQuery(string $query, array $variables = []): array
    {
        try {
            $response = $this->client->post('graphql', [
                'json' => [
                    'query' => $query,
                    'variables' => $variables
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(), true);
            }
            
            throw new \Exception("GraphQL request failed with status: " . $response->getStatusCode());
            
        } catch (GuzzleException $e) {
            Log::error("GraphQL query failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update issue title and body
     */
    public function updateIssueWithTitleAndBody(int $issueNumber, string $title, string $body): bool
    {
        try {
            $response = $this->client->patch("repos/{$this->repo}/issues/{$issueNumber}", [
                'json' => [
                    'title' => $title,
                    'body' => $body,
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error("Failed to update GitHub issue #{$issueNumber} title and body: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update title with status emoji and WIP indicator
     */
    private function updateTitleWithStatusEmoji($title, $status)
    {
        // Limpiar emojis de estado y [WIP] existentes
        $cleanTitle = preg_replace('/^🟡\s*|^🟠\s*|^🟢\s*/', '', $title);
        $cleanTitle = preg_replace('/\s*\[WIP\]\s*/', '', $cleanTitle);
        $cleanTitle = trim($cleanTitle);
        
        switch ($status) {
            case 'in-progress':
                return "🟡 [WIP] {$cleanTitle}";
            case 'waiting':
                return "🟠 [WIP] {$cleanTitle}";
            case 'completed':
                return "🟢 {$cleanTitle}";
            default:
                return $cleanTitle;
        }
    }

    /**
     * Update issue with complete file content (full sync)
     */
    public function updateIssueWithFullContent(int $issueNumber, string $fullContent): bool
    {
        return $this->updateIssue($issueNumber, $fullContent);
    }

    /**
     * Add status indicator to content
     */
    private function addStatusIndicator(string $content, string $status): string
    {
        // Debug logging
        Log::info("GitHubService: addStatusIndicator called", [
            'status' => $status,
            'status_type' => gettype($status)
        ]);
        
        $statusEmojis = [
            'in-progress' => '🟡 **In Progress**',
            'waiting' => '🟠 **Waiting**',
            'completed' => '🟢 **Completed**',
            'none' => ''
        ];

        $statusIndicator = $statusEmojis[$status] ?? '';
        
        Log::info("GitHubService: Status indicator resolved", [
            'status' => $status,
            'statusIndicator' => $statusIndicator,
            'available_statuses' => array_keys($statusEmojis)
        ]);
        
        if (empty($statusIndicator)) {
            return $content;
        }

        // Add status at the beginning of content
        $statusSection = "<!-- STATUS_INDICATOR -->\n{$statusIndicator}\n<!-- /STATUS_INDICATOR -->\n\n";
        
        // Remove existing status if present
        $content = preg_replace('/<!-- STATUS_INDICATOR -->.*?<!-- \/STATUS_INDICATOR -->\s*/s', '', $content);
        
        return $statusSection . $content;
    }

    /**
     * Detect current task status from sessions
     */
    public function detectTaskStatus(array $sessions): string
    {
        if (empty($sessions)) {
            return 'none';
        }

        // Check if there's an active session (end = HH:MM)
        foreach ($sessions as $session) {
            if ($session['end'] === 'HH:MM') {
                return 'in-progress';
            }
        }

        // No active sessions, task is either waiting or completed
        // This would need additional logic to distinguish between waiting and completed
        // For now, we'll determine this based on explicit calls
        return 'waiting';
    }

    /**
     * Update issue with time tracking information and sessions
     * @deprecated Use updateIssueWithFullContent for complete sync
     */
    public function updateIssueWithTimeTracking(int $issueNumber, array $timeTrackingData, array $sessions = []): bool
    {
        $issue = $this->fetchIssue($issueNumber);
        if (!$issue) {
            return false;
        }

        $currentBody = $issue['body'] ?? '';
        
        // Update both the original time tracking section and the new visual section
        $updatedBody = $this->updateOriginalTimeSection($currentBody, $sessions, $timeTrackingData['tracked_time'] ?? 0);
        $updatedBody = $this->updateTimeTrackingSection($updatedBody, $timeTrackingData);

        return $this->updateIssue($issueNumber, $updatedBody);
    }

    /**
     * Update the original ## Time Tracking section with sessions table
     */
    private function updateOriginalTimeSection(string $body, array $sessions, int $trackedTime): string
    {
        // Build the sessions table
        $tableRows = [];
        foreach ($sessions as $session) {
            $tableRows[] = "| {$session['date']} | {$session['start']} | {$session['end']} |";
        }
        
        $sessionsTable = empty($tableRows) ? 
            "| Date | Start | End |\n|------|-------|-----|" : 
            "| Date | Start | End |\n|------|-------|-----|\n" . implode("\n", $tableRows);

        // Format tracked time
        $trackedTimeFormatted = $this->formatMinutes($trackedTime);
        
        $newTimeSection = "## Time Tracking\n\n{$sessionsTable}\n\n**Total tracked:** {$trackedTimeFormatted}";
        
        // Pattern to match the original time tracking section
        $pattern = '/## Time Tracking\s*\n\n.*?\*\*Total tracked:\*\*[^\n]*/s';
        
        if (preg_match($pattern, $body)) {
            return preg_replace($pattern, $newTimeSection, $body);
        }
        
        return $body;
    }

    /**
     * Format minutes to human readable format
     */
    private function formatMinutes(int $minutes): string
    {
        if ($minutes === 0) {
            return '0m';
        }

        $hours = intval($minutes / 60);
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
     * Update or add time tracking section in issue body
     */
    private function updateTimeTrackingSection(string $body, array $data): string
    {
        $timeTrackingSection = $this->buildTimeTrackingSection($data);
        
        // Check if time tracking section already exists
        $pattern = '/<!-- TIME_TRACKING_START -->.*?<!-- TIME_TRACKING_END -->/s';
        
        if (preg_match($pattern, $body)) {
            // Update existing section
            return preg_replace($pattern, $timeTrackingSection, $body);
        } else {
            // Add new section at the end
            return $body . "\n\n" . $timeTrackingSection;
        }
    }

    /**
     * Build time tracking section markdown
     */
    private function buildTimeTrackingSection(array $data): string
    {
        $section = "<!-- TIME_TRACKING_START -->\n";
        $section .= "## ⏱️ Time Tracking\n\n";
        
        if (isset($data['status'])) {
            $statusEmoji = $data['status'] === 'in-progress' ? '🟡' : '🟢';
            $section .= "**Status:** {$statusEmoji} " . ucfirst(str_replace('-', ' ', $data['status'])) . "\n";
        }

        if (isset($data['start_time'])) {
            $section .= "**Started:** {$data['start_time']}\n";
        }

        if (isset($data['end_time'])) {
            $section .= "**Completed:** {$data['end_time']}\n";
        }

        if (isset($data['tracked_time'])) {
            $section .= "**Time Tracked:** {$data['tracked_time']} minutes\n";
        }

        if (isset($data['estimated_time'])) {
            $section .= "**Estimated Time:** {$data['estimated_time']} minutes\n";
        }

        if (isset($data['efficiency'])) {
            $section .= "**Efficiency:** {$data['efficiency']}%\n";
        }

        $section .= "\n<!-- TIME_TRACKING_END -->";
        
        return $section;
    }

    /**
     * Extract repository owner and name from various formats
     */
    public static function parseRepo(string $repo): string
    {
        // Handle different formats: owner/repo, https://github.com/owner/repo, etc.
        if (preg_match('/github\.com\/([^\/]+\/[^\/]+)/', $repo, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/^([^\/]+\/[^\/]+)$/', $repo, $matches)) {
            return $matches[1];
        }

        throw new \InvalidArgumentException("Invalid GitHub repository format: {$repo}");
    }
}