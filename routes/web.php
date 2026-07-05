<?php

use App\Http\Controllers\PdfController;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/pdf/invoice/{invoice}', [PdfController::class, 'invoice'])->name('pdf.invoice');
Route::get('/pdf/contract/{id}', [PdfController::class, 'printContract'])->name('pdf.contract');
Route::get('/pdf/handoff', [PdfController::class, 'printHandoff'])->name('pdf.handoff');

Route::get('/admin/quick-sale/receipt/{invoice}', function (Invoice $invoice) {
    abort_unless(auth()->check(), 403);

    $invoice->load(['client', 'items.product']);

    $subtotal = (float) $invoice->subtotal;
    $taxAmount = (float) $invoice->tax_amount;
    $receipt = [
        'number' => $invoice->invoice_number ?? 'POS-'.$invoice->id,
        'invoice_number' => $invoice->invoice_number ?? 'POS-'.$invoice->id,
        'date' => optional($invoice->invoice_date)->format('d/m/Y') ?? now()->format('d/m/Y H:i'),
        'sale_date' => optional($invoice->invoice_date)->format('d/m/Y') ?? now()->format('d/m/Y H:i'),
        'currency' => $invoice->currency ?? 'OMR',
        'customer_name' => $invoice->client?->name ?? 'Walk-in Customer',
        'customer_phone' => $invoice->client?->phone_mobile ?? '',
        'vat_trn' => $invoice->client?->vat_number ?? '',
        'customer_address' => $invoice->client?->address ?? '',
        'payment_method' => $invoice->payments()->latest()->value('payment_method') ?? 'Cash',
        'subtotal' => number_format($subtotal, 3, '.', ''),
        'tax_amount' => number_format($taxAmount, 3, '.', ''),
        'total_amount' => number_format((float) $invoice->total_amount, 3, '.', ''),
        'items' => $invoice->items->map(function (InvoiceItem $item) use ($subtotal, $taxAmount): array {
            $lineTotal = (float) $item->line_total;
            $lineSubtotal = $taxAmount > 0 && $subtotal > 0
                ? round($lineTotal / (1 + ($taxAmount / $subtotal)), 3)
                : $lineTotal;

            return [
                'name' => $item->product?->name ?? 'Product #'.$item->product_id,
                'quantity' => number_format((float) $item->quantity, 3, '.', ''),
                'unit_price' => number_format((float) $item->unit_price, 3, '.', ''),
                'tax_amount' => number_format(max($lineTotal - $lineSubtotal, 0), 3, '.', ''),
                'line_total' => number_format($lineTotal, 3, '.', ''),
            ];
        })->all(),
    ];

    $pdf = Pdf::loadView('receipts.pos', ['receipt' => $receipt]);

    return $pdf->download($receipt['number'].'.pdf');
})->middleware('auth')->name('quick-sale.receipt');

Route::get('/admin/quick-sale/process', function (Request $request) {
    abort_unless(auth()->check(), 403);

    $payload = $request->query('data');
    abort_if(blank($payload), 422, 'Missing sale data.');

    $data = json_decode(base64_decode($payload), true, 512, JSON_THROW_ON_ERROR);
    $items = collect($data['items'] ?? [])->filter(fn (array $item) => filled($item['product_id'] ?? null));
    abort_if($items->isEmpty(), 422, 'A quick sale requires at least one product.');

    $receipt = DB::transaction(function () use ($data, $items): array {
        $customerName = trim((string) ($data['customer_name'] ?? 'Walk-in Customer')) ?: 'Walk-in Customer';
        $customerPhone = trim((string) ($data['customer_phone'] ?? ''));
        $vatTrn = trim((string) ($data['vat_trn'] ?? ''));
        $customerAddress = trim((string) ($data['customer_address'] ?? ''));
        $saleDate = $data['sale_date'] ?? today()->format('Y-m-d');
        $currency = in_array($data['currency'] ?? 'OMR', ['OMR', 'AED', 'USD'], true) ? $data['currency'] : 'OMR';
        $vatRate = max((float) ($data['vat_rate'] ?? 5), 0);

        $clientAttributes = [
            'name' => $customerName,
            'country' => 'Oman',
            'is_active' => true,
            'user_id' => auth()->id(),
            'created_by' => auth()->id(),
        ];

        if (filled($vatTrn)) {
            $clientAttributes['vat_number'] = $vatTrn;
        }

        if (filled($customerAddress)) {
            $clientAttributes['address'] = $customerAddress;
        }

        $client = filled($vatTrn)
            ? Client::query()->where('vat_number', $vatTrn)->first()
            : null;

        $client ??= filled($customerPhone)
            ? Client::query()->where('phone_mobile', $customerPhone)->first()
            : null;

        $client ??= Client::create(array_merge($clientAttributes, filled($customerPhone) ? ['phone_mobile' => $customerPhone] : []));

        $clientUpdates = [];
        if (filled($customerPhone) && blank($client->phone_mobile)) {
            $clientUpdates['phone_mobile'] = $customerPhone;
        }
        if (filled($vatTrn) && blank($client->vat_number)) {
            $clientUpdates['vat_number'] = $vatTrn;
        }
        if (filled($customerAddress) && blank($client->address)) {
            $clientUpdates['address'] = $customerAddress;
        }
        if ($clientUpdates !== []) {
            $client->forceFill($clientUpdates)->save();
        }

        $lines = [];
        $subtotal = 0.0;
        $taxAmount = 0.0;

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $quantity = max((float) ($item['quantity'] ?? 1), 0.001);
            $unitPrice = (float) ($product->default_price ?? $item['price'] ?? 0);
            $lineSubtotal = round($quantity * $unitPrice, 3);
            $lineTax = round($lineSubtotal * ($vatRate / 100), 3);
            $lineTotal = round($lineSubtotal + $lineTax, 3);

            $subtotal += $lineSubtotal;
            $taxAmount += $lineTax;

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_subtotal' => $lineSubtotal,
                'tax_amount' => $lineTax,
                'line_total' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 3);
        $taxAmount = round($taxAmount, 3);
        $totalAmount = round($subtotal + $taxAmount, 3);
        $invoiceNumber = 'POS-'.now()->format('YmdHis').'-'.random_int(100, 999);

        $order = Order::create([
            'user_id' => auth()->id(),
            'client_id' => $client->id,
            'order_date' => $saleDate,
            'status' => 'Completed',
            'tax_amount' => number_format($taxAmount, 3, '.', ''),
            'total_amount' => number_format($totalAmount, 3, '.', ''),
            'created_by' => auth()->id(),
        ]);

        $invoice = Invoice::create([
            'user_id' => auth()->id(),
            'invoice_number' => $invoiceNumber,
            'client_id' => $client->id,
            'order_id' => $order->id,
            'invoice_date' => $saleDate,
            'due_date' => $saleDate,
            'status' => 'Paid',
            'subtotal' => number_format($subtotal, 3, '.', ''),
            'vat_amount' => number_format($taxAmount, 3, '.', ''),
            'tax_amount' => number_format($taxAmount, 3, '.', ''),
            'total_amount' => number_format($totalAmount, 3, '.', ''),
            'currency' => $currency,
            'created_by' => auth()->id(),
        ]);

        foreach ($lines as $line) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $line['product']->id,
                'quantity' => number_format($line['quantity'], 3, '.', ''),
                'unit_price' => number_format($line['unit_price'], 3, '.', ''),
                'total_price' => number_format($line['line_total'], 3, '.', ''),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $line['product']->id,
                'quantity' => number_format($line['quantity'], 3, '.', ''),
                'unit_price' => number_format($line['unit_price'], 3, '.', ''),
                'line_total' => number_format($line['line_total'], 3, '.', ''),
            ]);

            if (! is_null($line['product']->stock_quantity)) {
                $line['product']->decrement('stock_quantity', (int) ceil($line['quantity']));
            }
        }

        Payment::create([
            'user_id' => auth()->id(),
            'invoice_id' => $invoice->id,
            'client_id' => $client->id,
            'amount' => number_format($totalAmount, 3, '.', ''),
            'payment_date' => $saleDate,
            'payment_method' => $data['payment_method'] ?? 'Cash',
            'reference_number' => $invoiceNumber,
            'created_by' => auth()->id(),
        ]);

        return [
            'number' => $invoiceNumber,
            'invoice_number' => $invoiceNumber,
            'date' => \Carbon\Carbon::parse($saleDate)->format('d/m/Y'),
            'sale_date' => \Carbon\Carbon::parse($saleDate)->format('d/m/Y'),
            'currency' => $currency,
            'customer_name' => $client->name,
            'customer_phone' => $customerPhone,
            'vat_trn' => $vatTrn,
            'customer_address' => $customerAddress,
            'payment_method' => $data['payment_method'] ?? 'Cash',
            'subtotal' => number_format($subtotal, 3, '.', ''),
            'tax_amount' => number_format($taxAmount, 3, '.', ''),
            'total_amount' => number_format($totalAmount, 3, '.', ''),
            'items' => collect($lines)->map(fn (array $line) => [
                'name' => $line['product']->name,
                'quantity' => number_format($line['quantity'], 3, '.', ''),
                'unit_price' => number_format($line['unit_price'], 3, '.', ''),
                'tax_amount' => number_format($line['tax_amount'], 3, '.', ''),
                'line_total' => number_format($line['line_total'], 3, '.', ''),
            ])->all(),
        ];
    });

    $pdf = Pdf::loadView('receipts.pos', ['receipt' => $receipt]);

    return $pdf->download($receipt['number'].'.pdf');
})->middleware('auth')->name('quick-sale.process');

require __DIR__.'/auth.php';
