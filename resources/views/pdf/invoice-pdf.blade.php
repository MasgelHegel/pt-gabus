<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-table td {
            padding: 0;
            vertical-align: top;
        }
        .company-logo {
            width: 50%;
        }
        .company-logo img {
            height: 75px;
        }
        .company-info {
            width: 50%;
            text-align: right;
            font-size: 9.5pt;
            line-height: 1.4;
        }
        .divider {
            border-bottom: 2.5px solid #1e3a8a; /* Navy blue divider */
            margin-top: 12px;
            margin-bottom: 25px;
            width: 100%;
        }
        .title {
            font-size: 28pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-table td {
            vertical-align: top;
            padding: 0;
        }
        .customer-info {
            width: 55%;
            font-size: 11pt;
            line-height: 1.4;
        }
        .invoice-meta {
            width: 45%;
        }
        .invoice-meta table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-meta td {
            padding: 3px 2px;
            font-size: 11pt;
        }
        .invoice-meta td.label {
            width: 130px;
            text-align: left;
        }
        .invoice-meta td.colon {
            width: 15px;
            text-align: center;
        }
        .invoice-meta td.value {
            text-align: left;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            margin-bottom: 20px;
        }
        .items-table th {
            border: 1px solid #7f8c8d;
            padding: 6px 8px;
            font-weight: bold;
            text-align: left;
            font-size: 10pt;
        }
        .items-table td {
            border: 1px solid #7f8c8d;
            padding: 6px 8px;
            font-size: 10.5pt;
            height: 22px;
            vertical-align: middle;
        }
        .total-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .total-table td {
            padding: 4px 0;
            font-size: 11pt;
        }
        .thanks {
            width: 50%;
            font-style: italic;
            font-weight: bold;
        }
        .total-due-label {
            width: 25%;
            text-align: right;
            font-weight: bold;
            padding-right: 15px;
        }
        .total-due-value {
            width: 25%;
            text-align: right;
            font-weight: bold;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .footer-table td {
            vertical-align: top;
            padding: 0;
        }
        .bank-info {
            width: 55%;
            font-size: 11pt;
        }
        .bank-info table {
            border-collapse: collapse;
        }
        .bank-info td {
            padding: 4px 0;
        }
        .bank-info td.label {
            width: 110px;
            font-weight: bold;
            border-bottom: 1.5px solid #333;
        }
        .bank-info td.colon {
            width: 20px;
            text-align: center;
        }
        .bank-info td.value {
            text-align: left;
        }
        .signature-area {
            width: 45%;
            text-align: center;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 11pt;
        }
        .signature-logo {
            height: 55px;
            margin: 2px auto;
            display: block;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 11pt;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="company-logo">
                <img src="{{ public_path('image/logo.jpg') }}" alt="PT GABUS GAS TRUUSSS Logo">
            </td>
            <td class="company-info">
                <strong>Perum Buana Indah Garden Blok C No 6 Desa Srijaya</strong><br>
                Kec Tambun Utara, Kab Bekasi, Jawa Barat<br>
                gabusgastruusss@gmail.com<br>
                0822-2042-7053
            </td>
        </tr>
    </table>

    <!-- Horizontal Blue Divider Line -->
    <div class="divider"></div>

    <!-- Title -->
    <div class="title">INVOICE</div>

    <!-- Metadata Section (Customer and Invoice Details) -->
    <table class="meta-table">
        <tr>
            <td class="customer-info">
                <strong>GABUS GAS TRUUSSS</strong><br>
                @if($invoice->customer?->company_name)
                    {{ $invoice->customer->company_name }}<br>
                @endif
                @if($invoice->customer?->address)
                    {{ $invoice->customer->address }}<br>
                @endif
                <br>
                Nama Customer : {{ $invoice->customer?->name ?? '—' }}
            </td>
            <td class="invoice-meta">
                <table>
                    <tr>
                        <td class="label">Invoice No</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Delivery Note</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $invoice->salesOrder?->shipment?->shipment_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Invoice Date</td>
                        <td class="colon">:</td>
                        <td class="value">
                            @php
                                $monthsEng = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                $monthsInd = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                $formattedDate = $invoice->invoice_date?->format('d F Y') ?? '—';
                                $formattedDate = str_replace($monthsEng, $monthsInd, $formattedDate);
                            @endphp
                            {{ $formattedDate }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">Keterangan</th>
                <th style="width: 10%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Unit Harga</th>
                <th style="width: 15%; text-align: center;">Discount</th>
                <th style="width: 15%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $rowCount = 0; @endphp
            @foreach($invoice->items as $item)
                @php $rowCount++; @endphp
                <tr>
                    <td>{{ $item->product?->name ?? 'Produk' }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td style="text-align: center;">—</td>
                    <td style="text-align: right;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            
            <!-- Fill remaining rows to reach exactly 5 rows in the layout -->
            @for($i = $rowCount; $i < 5; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Total Section -->
    <table class="total-table">
        <tr>
            <td class="thanks">Thanks For Your Bussiness!</td>
            <td class="total-due-label">Total Due:</td>
            <td class="total-due-value">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- Footer Section -->
    <table class="footer-table">
        <tr>
            <td class="bank-info">
                <strong>Bank Information</strong>
                @php
                    $bankInfo = $invoice->getBankInformation();
                @endphp
                <table style="margin-top: 5px;">
                    <tr>
                        <td class="label">Bank Name</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $bankInfo['account_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="label">Bank</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $bankInfo['bank_name'] }}</td>
                    </tr>
                    @if($bankInfo['account_no'] !== '—')
                    <tr>
                        <td class="label">Account No</td>
                        <td class="colon">:</td>
                        <td class="value">{{ $bankInfo['account_no'] }}</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td class="signature-area">
                <div class="signature-title">Hormat Kami,</div>
                <img class="signature-logo" src="{{ public_path('image/logo.jpg') }}" alt="Stamp/Logo">
                <div class="signature-name">Rizka Firlana</div>
            </td>
        </tr>
    </table>

</body>
</html>
