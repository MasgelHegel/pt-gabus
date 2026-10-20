<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderWorkflowService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ── SALES REVIEW ──────────────────────────────────────────
            Actions\Action::make('sales_review')
                ->label('Sales Review')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->visible(fn (): bool =>
                    in_array($this->record->status, [OrderStatus::Submitted, OrderStatus::Draft])
                )
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Sales Review')
                ->modalDescription(fn (): string =>
                    "Order {$this->record->order_number} akan ditandai sudah ditinjau Sales."
                )
                ->action(function (OrderWorkflowService $service): void {
                    $service->salesReviewOrder($this->record, (int) auth()->id());

                    Notification::make()
                        ->title("Order {$this->record->order_number} sudah ditinjau Sales")
                        ->success()->send();

                    $this->refreshFormData(['status', 'sales_id']);
                }),

            // ── APPROVE & BUAT SO ─────────────────────────────────────
            Actions\Action::make('admin_approve')
                ->label('Approve & Buat SO')
                ->icon('heroicon-o-document-check')
                ->color('success')
                ->visible(fn (): bool =>
                    in_array($this->record->status, [OrderStatus::Submitted, OrderStatus::SalesReviewed])
                )
                ->modalHeading('Approve Order & Buat Sales Order')
                ->modalDescription(fn (): string =>
                    "Approve order {$this->record->order_number}? Sales Order, Invoice, dan Piutang akan dibuat otomatis."
                )
                ->form([
                    Forms\Components\Select::make('sales_id')
                        ->label('Sales Handler')
                        ->options(function () {
                            $salesUsers = User::role('sales')
                                ->whereNull('deleted_at')
                                ->pluck('name', 'id')
                                ->toArray();

                            if (empty($salesUsers)) {
                                $salesUsers = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['sales', 'admin', 'super_admin']))
                                    ->whereNull('deleted_at')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }

                            if (auth()->check() && ! isset($salesUsers[auth()->id()])) {
                                $salesUsers[auth()->id()] = auth()->user()->name;
                            }

                            return $salesUsers;
                        })
                        ->default(function () {
                            if ($this->record->sales_id) {
                                return $this->record->sales_id;
                            }

                            $currentUser = auth()->user();
                            if ($currentUser?->hasRole('sales')) {
                                return $currentUser->id;
                            }

                            return User::role('sales')->whereNull('deleted_at')->value('id') ?? $currentUser?->id;
                        })
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->helperText('Pilih sales handler untuk Sales Order ini.'),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Jatuh Tempo Invoice')
                        ->required()
                        ->native(false)
                        ->format('Y-m-d')
                        ->default(fn () => now()->addDays($this->record->customer?->due_period_days ?? 30)->format('Y-m-d'))
                        ->displayFormat('d/m/Y')
                        ->helperText('Tentukan kapan invoice harus dibayar oleh customer.')
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $dueDateStr = $state instanceof \DateTimeInterface 
                                    ? $state->format('Y-m-d') 
                                    : (string) $state;
                                $targetDate = now()->addDays(7)->format('Y-m-d');
                                if ($dueDateStr !== $targetDate) {
                                    $set('due_preset', null);
                                } else {
                                    $set('due_preset', '7');
                                }
                            }
                        }),

                    Forms\Components\Radio::make('due_preset')
                        ->label('Atau pilih cepat')
                        ->options([
                            '7'  => '7 hari',
                        ])
                        ->default(fn () => $this->record->customer?->due_period_days === 7 ? '7' : null)
                        ->inline()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $set('due_date', now()->addDays((int) $state)->format('Y-m-d'));
                            }
                        }),

                    Forms\Components\Placeholder::make('info')
                        ->label('')
                        ->content('Setelah approve: Sales Order + Invoice + Piutang customer dibuat otomatis.'),
                ])
                ->action(function (array $data, OrderWorkflowService $service): void {
                    $dueDateRaw = $data['due_date'] ?? null;
                    if ($dueDateRaw instanceof \DateTimeInterface) {
                        $dueDate = \Carbon\Carbon::instance($dueDateRaw)->startOfDay();
                    } elseif (! empty($dueDateRaw)) {
                        if (str_contains((string) $dueDateRaw, '/')) {
                            try {
                                $dueDate = \Carbon\Carbon::createFromFormat('d/m/Y', (string) $dueDateRaw)->startOfDay();
                            } catch (\Exception $e) {
                                $dueDate = \Carbon\Carbon::parse((string) $dueDateRaw)->startOfDay();
                            }
                        } else {
                            $dueDate = \Carbon\Carbon::parse((string) $dueDateRaw)->startOfDay();
                        }
                    } else {
                        $dueDate = now()->addDays($this->record->customer?->due_period_days ?? 30)->startOfDay();
                    }

                    $dueDays = (int) now()->diffInDays($dueDate, false);
                    $dueDays = max(1, $dueDays);

                    if (! empty($data['sales_id'])) {
                        $this->record->update(['sales_id' => $data['sales_id']]);
                    }

                    $so = $service->adminApproveOrder($this->record, (int) auth()->id(), 0.0, $dueDays, $dueDate);

                    Notification::make()
                        ->title("Order Diapprove! SO #{$so->so_number} Dibuat")
                        ->body("Invoice jatuh tempo: {$dueDate->format('d M Y')} ({$dueDays} hari).")
                        ->success()->send();

                    $this->refreshFormData(['status', 'sales_id']);
                }),

            // ── TOLAK ─────────────────────────────────────────────────
            Actions\Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool =>
                    in_array($this->record->status, [
                        OrderStatus::Submitted,
                        OrderStatus::Draft,
                        OrderStatus::SalesReviewed,
                    ])
                )
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Alasan Penolakan')
                        ->required()->rows(3),
                ])
                ->action(function (array $data): void {
                    $oldStatus = $this->record->status;

                    $this->record->update([
                        'status' => OrderStatus::Rejected,
                        'notes'  => $data['notes'],
                    ]);

                    try {
                        OrderStatusChanged::dispatch($this->record->fresh(), $oldStatus, $this->record->status);
                    } catch (\Throwable $e) {
                        Log::warning('Gagal broadcast OrderStatusChanged pada reject order: ' . $e->getMessage());
                    }

                    Notification::make()
                        ->title("Order {$this->record->order_number} Ditolak")
                        ->warning()->send();

                    $this->refreshFormData(['status', 'notes']);
                }),
        ];
    }
}
