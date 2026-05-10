<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('sentinel.project_name', 'My Project') }} — Sentinel</title>
    <link rel="stylesheet" href="{{ asset('vendor/sentinel/assets/app.css') }}">
</head>
<body>
<div id="app"></div>
<script type="module" src="{{ asset('vendor/sentinel/assets/app.js') }}"></script>
</body>
</html>
