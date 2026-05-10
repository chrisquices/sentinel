<?php

return [

    'path' => env('SENTINEL_PATH', 'sentinel'),

    'middleware' => ['web'],

    'project_name' => env('APP_NAME', 'My Project'),

    'pagination' => 20,

    /*
     * How often (in seconds) the frontend polls the queue and log tail endpoints.
     */
    'poll_interval' => 3,

    /*
     * How often (in seconds) the frontend polls the scheduler endpoint.
     */
    'scheduler_poll_interval' => 10,

    /*
     * Published asset version string — appended as ?v= to CSS/JS URLs to bust caches after updates.
     */
    'version' => '1.0.0',

];
