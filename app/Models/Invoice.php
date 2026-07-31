<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'sales_order_id',
        'customer_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'gasback',
        'status',
    ];

    protected $casts = [
        'status'       => InvoiceStatus::class,
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'subtotal'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'gasback'      => 'integer',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function getBankInformation(): array
    {
        // Default to BCA
        $bank = 'BCA';
        $accountNo = '8421573832';
        $accountName = 'Rizka Firlana';

        // Check if there is a payment linked to this invoice (verified or pending)
        $payment = $this->payments()
            ->latest()
            ->first();

        if ($payment && $payment->account) {
            $name = $payment->account->name;
            if (stripos($name, 'Mandiri') !== false) {
                $bank = 'Mandiri';
                $accountNo = '1560012345678'; // Placeholder Mandiri number
                $accountName = 'Rizka Firlana';
            } elseif (stripos($name, 'BCA') !== false) {
                $bank = 'BCA';
                $accountNo = '8421573832';
                $accountName = 'Rizka Firlana';
            } else {
                $bank = $name;
                $accountNo = '—';
                $accountName = 'Rizka Firlana';
            }
        }

        return [
            'bank_name' => $bank,
            'account_no' => $accountNo,
            'account_name' => $accountName,
        ];
    }

    public function getWhatsAppUrl(): ?string
    {
        $customerPhone = $this->customer?->phone;
        if (! $customerPhone) {
            return null;
        }

        // Clean phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $customerName = $this->customer?->name ?? 'Pelanggan';
        $companyName = $this->customer?->company_name ?? '—';
        $invoiceNo = $this->invoice_number;
        
        $this->loadMissing(['customer', 'items.product', 'salesOrder.shipment']);
        
        $deliveryNote = $this->salesOrder?->shipment?->shipment_number ?? '—';
        $invoiceDate = $this->invoice_date?->format('d F Y') ?? '—';
        $dueDate = $this->due_date?->format('d F Y') ?? '—';
        
        // Indonesian months translation
        $monthsEng = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $monthsInd = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $invoiceDate = str_replace($monthsEng, $monthsInd, $invoiceDate);
        $dueDate = str_replace($monthsEng, $monthsInd, $dueDate);

        $itemsText = "";
        foreach ($this->items as $item) {
            $productName = $item->product?->name ?? 'Produk';
            $qty = $item->quantity;
            $unitPrice = number_format((float) $item->unit_price, 0, ',', '.');
            $subtotal = number_format((float) $item->subtotal, 0, ',', '.');
            $itemsText .= "- {$productName} (x{$qty}) : Rp {$subtotal}\n";
        }

        $totalAmount = number_format((float) $this->total_amount, 0, ',', '.');
        $downloadUrl = \Illuminate\Support\Facades\URL::signedRoute('invoice.pdf.download', ['invoice' => $this->id]);
        $bankInfo = $this->getBankInformation();

        $text = "*PT GABUS GAS TRUUSSS*\n";
        $text .= "Perum Buana Indah Garden Blok C No 6 Desa Srijaya\n";
        $text .= "Kec Tambun Utara, Kab Bekasi, Jawa Barat\n";
        $text .= "gabusgastruusss@gmail.com | 0822-2042-7053\n";
        $text .= "─────────────────────────────\n";
        $text .= "*INVOICE*\n";
        $text .= "─────────────────────────────\n";
        $text .= "*No. Invoice:* " . $invoiceNo . "\n";
        $text .= "*Delivery Note:* " . $deliveryNote . "\n";
        $text .= "*Tanggal:* " . $invoiceDate . "\n";
        $text .= "*Jatuh Tempo:* " . $dueDate . "\n\n";
        $text .= "*Nama Customer:* " . $customerName . "\n";
        if ($companyName && $companyName !== '—') {
            $text .= "*Nama Dapur/Perusahaan:* " . $companyName . "\n";
        }
        $text .= "\n*Rincian Tagihan:*\n" . $itemsText;
        $text .= "\n*Total Tagihan:* *Rp " . $totalAmount . "*\n";
        $text .= "─────────────────────────────\n";
        $text .= "*Unduh Invoice Resmi (PDF):*\n";
        $text .= $downloadUrl . "\n";
        $text .= "─────────────────────────────\n";
        $text .= "*Informasi Pembayaran (Transfer Bank):*\n";
        $text .= "Bank: *" . $bankInfo['bank_name'] . "*\n";
        if ($bankInfo['account_no'] !== '—') {
            $text .= "No. Rekening: *" . $bankInfo['account_no'] . "*\n";
        }
        $text .= "A.N.: *" . $bankInfo['account_name'] . "*\n\n";
        $text .= "Terima kasih atas kerja samanya!\n";
        $text .= "*Hormat Kami,*\n";
        $text .= "*PT GABUS GAS TRUUSSS*";

        return 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . rawurlencode($text);
    }

    protected static function booted(): void

    {
        static::deleting(function (Invoice $invoice) {
            if ($invoice->status !== \App\Enums\InvoiceStatus::Paid && $invoice->status !== \App\Enums\InvoiceStatus::Cancelled) {
                \App\Models\Customer::where('id', $invoice->customer_id)
                    ->decrement('piutang_balance', (float) $invoice->total_amount);
            }

            \App\Models\JournalEntry::where('reference', $invoice->invoice_number)->get()->each(function ($journal) {
                $journal->delete();
            });

            $invoice->payments()->get()->each(function ($payment) {
                $payment->delete();
            });

            $invoice->items()->delete();
        });
    }
}
