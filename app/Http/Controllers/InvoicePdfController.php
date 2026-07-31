<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoicePdfController extends Controller
{
    public function download(Request $request, Invoice $invoice)
    {
        // Enforce Laravel Signed URL signature validation
        if (! $request->hasValidSignature()) {
            abort(401, 'Tautan unduhan tidak sah atau kedaluwarsa.');
        }

        // Load relations needed for rendering
        $invoice->loadMissing(['customer', 'items.product', 'salesOrder.shipment']);

        // Generate PDF from the blade view
        $pdf = Pdf::loadView('pdf.invoice-pdf', compact('invoice'));

        // Stream the PDF to the browser (better user experience on mobile/desktop)
        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }
}
