<?php

declare(strict_types=1);

namespace App\Enums;

enum QueuePriority: string
{
    case High    = 'high';
    case Default = 'default';
    case Low     = 'low';
    case Reports = 'reports';
    case Mail    = 'mail';
    case Notifications = 'notifications';
}
