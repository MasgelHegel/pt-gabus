<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['order_number'])) {
            $data['order_number'] = \App\Services\SequenceService::generate('orders', 'order_number', 'ORD-' . now()->format('Ymd') . '-');
        }

        if (! empty($data['items'])) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) ($item['unit_price'] ?? 0);
                $total += $qty * $price;
            }
            $data['total_amount'] = $total;
        } elseif (! isset($data['total_amount']) || $data['total_amount'] === null) {
            $data['total_amount'] = 0;
        }

        return $data;
    }
}
