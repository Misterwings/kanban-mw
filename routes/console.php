<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tasks:send-reminders')
    ->dailyAt(config('kanban.reminder_time', '08:00'))
    ->timezone(config('kanban.timezone', 'America/Bogota'))
    ->withoutOverlapping();
