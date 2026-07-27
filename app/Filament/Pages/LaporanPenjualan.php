<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Exports\InvoicesExport;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\FontWeight;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

class LaporanPenjualan extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-currency-bangladeshi';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Penjualan';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.laporan-table';

    public function getTitle(): string
    {
        return 'Laporan Ringkasan Penjualan & Piutang';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-reports') || auth()->user()?->isSuperAdmin();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $query = $this->getFilteredTableQuery()
                        ->with(['customer', 'latestPayment', 'items.product'])
                        ->orderBy('invoice_date', 'desc')
                        ->orderBy('id', 'desc');

                    return Excel::download(new InvoicesExport($query), 'laporan-penjualan-' . now()->format('Ymd') . '.xlsx');
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Invoice::query()
                    ->with(['customer', 'items.product'])
                    ->orderBy('invoice_date', 'desc')
                    ->orderBy('id', 'desc')
            )
            ->heading('Ringkasan Penjualan')
            ->columns([
                TextColumn::make('invoice_number')->label('No. Invoice')->searchable()->sortable(),
                TextColumn::make('customer.name')->label('Customer')->searchable()->weight(FontWeight::Medium),
                
                TextColumn::make('items_list')
                    ->label('Detail Barang')
                    ->state(fn (Invoice $record): string => 
                        $record->items->map(fn ($item) => "{$item->product?->name} ({$item->quantity} {$item->product?->unit})")->join(', ')
                    )
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('invoice_date')->label('Tanggal Invoice')->date()->sortable(),
                TextColumn::make('due_date')->label('Jatuh Tempo')->date()->sortable(),
                TextColumn::make('total_amount')->label('Total Tagihan')->money('IDR')->sortable(),
                \Filament\Tables\Columns\TextInputColumn::make('gasback')
                    ->label('Gasback')
                    ->type('number')
                    ->alignRight()
                    ->disabled(fn () => ! auth()->user()?->hasAnyRole(['super_admin', 'admin']))
                    ->rules(['nullable', 'numeric', 'min:0'])
                    ->placeholder('0'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => $state->color())
                    ->formatStateUsing(fn ($state) => $state->label()),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(\App\Enums\InvoiceStatus::class)
                    ->native(false),

                \Filament\Tables\Filters\Filter::make('invoice_date')
                    ->label('Tanggal Invoice')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('invoice_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('invoice_date', '<=', $date));
                    }),
            ])
            ->actions([
                \Filament\Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isSuperAdmin() ?? false),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->isSuperAdmin() ?? false),
                ]),
            ])
            ->paginated(true);
    }
}
