<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfController extends Controller
{
    public function invoice(Invoice $invoice): StreamedResponse
    {
        $invoice->loadMissing(['client', 'items.product']);

        return Pdf::loadView('pdf.document', [
            'title' => sprintf('Invoice %s', $invoice->invoice_number ?? $invoice->id),
            'type' => 'Invoice',
            'invoice' => $invoice,
        ])->stream(sprintf('invoice-%s.pdf', $invoice->invoice_number ?? $invoice->id));
    }

    public function contract(Contract $contract): StreamedResponse
    {
        $contract->loadMissing(['client', 'items.product']);

        return Pdf::loadView('pdf.document', [
            'title' => sprintf('Contract %s', $contract->id),
            'type' => 'Contract',
            'contract' => $contract,
        ])->stream(sprintf('contract-%s.pdf', $contract->id));
    }
}
