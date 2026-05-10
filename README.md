<br />
<div align="center">
    <img src="./resources/img/logo.svg" alt="Logo" height="100">
</div>

<h1 align="center">Sentinel</h1>

<p align="center">
    A self-hosted Laravel dashboard for monitoring system resources, queues, scheduled tasks, and logs in real time. No external services required.<br />
    <br />
    <br />
    <a href="https://laravel.com">
        <img height="32" src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel Badge" />
    </a>
    <a href="https://www.php.net/">
        <img height="32" src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Badge" />
    </a>
    <a href="https://svelte.dev/">
        <img height="32" src="https://img.shields.io/badge/Svelte-FF3E00?style=for-the-badge&logo=svelte&logoColor=white" alt="Svelte Badge" />
    </a>
</p>

---

## Requirements

- **PHP** `^8.1`
- **Laravel** `10, 11, 12, or 13`

---

## Installation

1. **Add the VCS repository to `composer.json`**
   ```json
   "repositories": [
       {
           "type": "vcs",
           "url": "https://github.com/chrisquices/sentinel"
       }
   ]
   ```

2. **Require the package**
   ```bash
   composer require chrisquices/sentinel
   ```

3. **Publish config, views, and assets**
   ```bash
   php artisan vendor:publish --tag=sentinel --force
   ```

4. **Run the migrations**
   ```bash
   php artisan migrate
   ```

---

## Configuration

After publishing, edit `config/sentinel.php`:

```php
return [
    // URL path where the dashboard is served
    'path' => env('SENTINEL_PATH', 'sentinel'),

    // Middleware applied to all Sentinel routes
    'middleware' => ['web'],

    // Display name shown in the dashboard header
    'project_name' => env('APP_NAME', 'My Project'),

    // Number of log entries per page
    'pagination' => 20,

    // How often (seconds) the frontend polls the queue and log tail endpoints
    'poll_interval' => 3,

    // How often (seconds) the frontend polls the scheduler endpoint
    'scheduler_poll_interval' => 10,

    // Appended as ?v= to published CSS/JS URLs — increment after publishing new assets
    'version' => '1.0.0',
];
```

### Environment variables

| Variable | Default | Description |
|---|---|---|
| `SENTINEL_PATH` | `sentinel` | URL path for the dashboard |
| `APP_NAME` | `My Project` | Project name shown in the header |

---

## Authentication

Sentinel uses a `viewSentinel` gate to control access. By default it allows access in the `local` environment only. To customise this, define the gate in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewSentinel', function ($user) {
        return in_array($user?->email, [
            'admin@example.com',
        ]);
    });
}
```

The gate callback receives the currently authenticated user, or `null` if the request is unauthenticated. Unauthenticated requests that fail the gate return `401`. Authenticated requests that fail return `403`.

To allow access without any user (e.g. IP-based or environment-based):

```php
Gate::define('viewSentinel', function ($user) {
    return in_array(request()->ip(), ['127.0.0.1', '::1']);
});
```

---

## Features

### System
Displays live CPU usage (with a rolling 60-sample history graph), memory usage and availability, and disk storage — all read directly from the host OS.

### Runtime
Shows PHP version, SAPI, memory limit, max execution time, upload limits, and OPcache status (enabled state, hit ratio, memory used/free, cached script count).

### Scheduler
Lists all commands registered in the Laravel console kernel. For each task:
- Cron expression with a human-readable description
- Live countdown to the next run (updates every second)
- Last ran time and exit status (success / failed / never run)

Run history is stored in the `sentinel_scheduler_runs` table and persists across deployments. The frontend polls on the configured `scheduler_poll_interval`.

### Queue

Supports the **database** and **redis** queue drivers. If the app uses any other driver, the panel displays a clear message rather than empty tables.

Tabs: Pending, Processing, Completed, Failed — each with a live job count. Clicking any row opens a detail dialog showing queue, connection, attempts, run time, completed/failed timestamps, full exception trace, and raw payload.

Actions available per tab:
- **Failed** — retry a single job or bulk-delete all failed jobs
- **Completed** — delete a single job or bulk-delete all completed jobs

Completed job tracking is handled by Sentinel's own `sentinel_completed_jobs` table. The frontend polls on the configured `poll_interval`.

### Logs

Reads all configured Laravel log channels that have an existing file (single and daily drivers, including stack channels). Features:

- **Level filter** — All, Error, Warning, Info, Debug
- **Full-text search** — filters across all log entries for the active channel
- **Pagination** — controlled by the `pagination` config value
- **Live tail** — new log lines are automatically prepended every `poll_interval` seconds when on page 1 with no active search
- **Detail dialog** — click any row to see the full message and stack trace
- **Delete** — wipe the active channel's log file, with a confirmation dialog

Active channel and level filter are persisted in `localStorage` across sessions.

---

## Rate Limiting

All mutation endpoints (retry, delete, bulk-delete, log wipe) are protected by a rate limiter named `sentinel-mutations`, capped at **10 requests per minute per IP**. Responses beyond the limit return JSON `429`.

---

## Usage

Once installed, visit:

```
https://your-app.com/sentinel
```

Replace `sentinel` with the value of `path` in your config if you changed it.

---

## Local Development

After making changes to Sentinel, build the frontend assets, then update and republish them in the consuming app:

```bash
# In the sentinel package
npm run build

# In the consuming app
composer update chrisquices/sentinel
php artisan vendor:publish --tag=sentinel --force
```

After publishing new assets, increment the `version` key in `config/sentinel.php` to bust browser caches.
