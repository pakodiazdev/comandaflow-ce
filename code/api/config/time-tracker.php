<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Time Tracker Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Time Tracker package
    |
    */

    'github_token' => env('GITHUB_TOKEN'),
    'github_repo' => env('GITHUB_REPO', 'comandaflow-ce'),
    'task_path' => env('PATH_TASK', '/tasks'),
];
