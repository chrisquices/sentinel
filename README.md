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

1. **Require the package**
   ```bash
   composer require chrisquices/sentinel
   ```

2. **Publish config, views, and assets**
   ```bash
   php artisan vendor:publish --tag=sentinel --force
   ```

3. **Run the migrations**
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

    // Queue driver to inspect: 'database' or 'redis'
    'queue_driver' => env('SENTINEL_QUEUE_DRIVER', 'database'),

    // Display name shown in the dashboard header
    'project_name' => env('APP_NAME', 'My Project'),

    // Number of items per page in paginated views
    'pagination' => 15,
];
```

---

## Authentication

Sentinel uses a `viewSentinel` gate to control access. By default it allows access only in the `local` environment. To customise this, define the gate in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('viewSentinel', function ($user) {
        return in_array($user->email, [
            'admin@example.com',
        ]);
    });
}
```

Unauthenticated requests return a `401`. Authenticated requests that fail the gate return a `403`.

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
