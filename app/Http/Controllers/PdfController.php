<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function invoice(Invoice $invoice): Response
    {
        $invoice->loadMissing(['client', 'items.product']);

        return Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->stream(sprintf('invoice-%s.pdf', $invoice->invoice_number ?? $invoice->id));
    }

    public function contract(Contract $contract): Response
    {
        $contract->loadMissing(['client', 'items.product']);

        return Pdf::loadView('pdf.contract', [
            'contract' => $contract,
        ])->stream(sprintf('contract-%s.pdf', $contract->id));
    }
}
