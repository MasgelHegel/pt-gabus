<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Account;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;

class LaporanBukuBesar extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Buku Besar';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.laporan-table';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view-reports') || auth()->user()?->isSuperAdmin();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_excel')
                ->label('Ekspor Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\BukuBesarExport, 'laporan-buku-besar-' . now()->format('Ymd') . '.xlsx');
                }),
        ];
    }

    public function getTitle(): string
    {
        return 'Buku Besar';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\JournalLine::query()
                    ->with(['journalEntry', 'account'])
                    ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
                    ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
                    ->select('journal_lines.*', 'journal_entries.entry_number', 'journal_entries.entry_date', 'journal_entries.description as entry_description', 'accounts.code as account_code', 'accounts.name as account_name')
                    ->orderBy('accounts.code')
                    ->orderBy('journal_entries.entry_date')
            )
            ->heading('Buku Besar')
            ->columns([
                TextColumn::make('account_code')->label('Kode Akun')->searchable()->sortable(),
                TextColumn::make('account_name')->label('Nama Akun')->searchable()->weight(FontWeight::Medium),
                TextColumn::make('entry_date')->label('Tanggal')->date()->sortable(),
                TextColumn::make('entry_number')->label('No. Jurnal')->searchable(),
                TextColumn::make('entry_description')->label('Keterangan')->limit(30),
                TextColumn::make('debit')->label('Debet')->money('IDR')->color('danger'),
                TextColumn::make('credit')->label('Kredit')->money('IDR')->color('success'),
            ])
            ->filters([
                SelectFilter::make('account_id')
                    ->label('Akun')
                    ->options(Account::orderBy('code')->get()->mapWithKeys(fn (Account $a) => [
                        $a->id => "{$a->code} — {$a->name}",
                    ]))
                    ->native(false),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated(true);
    }
}
