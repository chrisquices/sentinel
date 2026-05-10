<?php

return [

    'path' => 'sentinel',

    'middleware' => ['web'],

    'project_name' => env('APP_NAME', 'My Project'),

    'pagination' => 20,

    /*
     * How often (in seconds) the frontend polls the queue and log tail endpoints.
     */
    'poll_interval' => 3,

];
