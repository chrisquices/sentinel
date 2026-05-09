<?php

return [

    'path' => env('SENTINEL_PATH', 'sentinel'),

    'middleware' => ['web'],

    /*
     * Queue driver to inspect: 'database' or 'redis'
     */
    'queue_driver' => env('SENTINEL_QUEUE_DRIVER', 'database'),

    'project_name' => env('APP_NAME', 'My Project'),

    'pagination' => 15,

];
