<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

// ============================================================
// Horizon snapshots — every 5 minutes
// ============================================================
Schedule::command('horizon:snapshot')->everyFiveMinutes();

// ============================================================
// Clear expired Sanctum tokens — daily at 01:00
// ============================================================
Schedule::command('sanctum:prune-expired --hours=24')->dailyAt('01:00');

// ============================================================
// Clear stale cache/session — daily at 02:00
// ============================================================
Schedule::command('cache:prune-stale-tags')->hourly();

// ============================================================
// Queue monitoring — prune failed jobs older than 7 days
// ============================================================
Schedule::command('queue:prune-failed --hours=168')->dailyAt('03:00');

// ============================================================
// Optimize (production only)
// ============================================================
Schedule::command('optimize')->dailyAt('04:00')->environments('production');
