<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QCStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QCCheck extends Model
{
    use HasFactory;

    protected $table = 'qc_checks';

    protected $fillable = [
        'goods_receipt_id',
        'inspector_id',
        'status',
        'passed_qty',
        'failed_qty',
        'notes',
    ];

    protected $casts = [
        'status' => QCStatus::class,
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
