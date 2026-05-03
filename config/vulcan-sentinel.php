<?php

return [

    'path' => env('VULCAN_SENTINEL_PATH', 'vulcan-sentinel'),

    'middleware' => ['web'],

    /*
     * Queue driver to inspect: 'database' or 'redis'
     */
    'queue_driver' => env('VULCAN_SENTINEL_QUEUE_DRIVER', 'database'),

    'project_name' => env('APP_NAME', 'My Project'),

    'pagination' => 15,

];
