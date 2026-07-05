<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Contract;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function invoice(Invoice $invoice): Response
    {
        $invoice->loadMissing(['client', 'items.product']);

        return Pdf::loadView('pdf.document', [
            'title' => sprintf('Invoice %s', $invoice->invoice_number ?? $invoice->id),
            'type' => 'Invoice',
            'record' => $invoice,
            'invoice' => $invoice,
        ])->stream(sprintf('invoice-%s.pdf', $invoice->invoice_number ?? $invoice->id));
    }

    public function contract(Contract $contract): Response
    {
        return $this->printContract($contract->id);
    }

    public function printContract($id): Response
    {
        $contract = Contract::with(['client', 'items.product', 'products'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.document', [
            'title' => sprintf('Contract %s', $contract->custom_id ?? $contract->id),
            'type' => 'Contract',
            'record' => $contract,
            'contract' => $contract,
        ]);

        $filename = ($contract->custom_id ?: 'contract-'.$id).'.pdf';

        return $pdf->stream($filename);
    }

    public function printHandoff(): Response
    {
        $orders = Order::query()
            ->whereDate('created_at', today())
            ->with('client')
            ->latest()
            ->get();

        $payments = Payment::query()
            ->whereDate('created_at', today())
            ->with('invoice')
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.handoff', [
            'orders' => $orders,
            'payments' => $payments,
        ]);

        return $pdf->stream('daily_handoff_'.now()->format('Y-m-d').'.pdf');
    }
}
