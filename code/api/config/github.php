<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GitHub Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for GitHub API integration, Projects, and time tracking
    |
    */

    'token' => env('GITHUB_TOKEN'),
    'repository' => env('GITHUB_REPOSITORY', 'comandaflow-ce'),
    'project_number' => env('GITHUB_PROJECT_NUMBER', 6),
    'project_id' => env('GITHUB_PROJECT_ID'),
    'status_field_id' => env('GITHUB_PROJECT_STATUS_FIELD_ID'),
    
    /*
    |--------------------------------------------------------------------------
    | GitHub Project Status Options
    |--------------------------------------------------------------------------
    |
    | Direct option IDs for GitHub Project V2 status field values
    |
    */
    'status_options' => [
        'waiting' => env('TIME_TRACKER_WAITING'),
        'in_progress' => env('TIME_TRACKER_IN_PROGRESS'),
        'completed' => env('TIME_TRACKER_COMPLETE'),
    ],

];