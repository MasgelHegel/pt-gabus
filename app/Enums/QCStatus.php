<?php

declare(strict_types=1);

namespace App\Enums;

enum QCStatus: string
{
    case Pending = 'pending';
    case Passed  = 'passed';
    case Failed  = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Menunggu QC',
            self::Passed  => 'Lolos QC',
            self::Failed  => 'Gagal QC',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'warning',
            self::Passed  => 'success',
            self::Failed  => 'danger',
        };
    }
}
