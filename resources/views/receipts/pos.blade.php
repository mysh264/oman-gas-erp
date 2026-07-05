<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>POS Receipt</title>
    <style>
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 0; padding: 28px; }
        .header { border-bottom: 2px solid #111827; margin-bottom: 22px; padding-bottom: 14px; }
        .brand { font-size: 22px; font-weight: 700; }
        .muted { color: #6b7280; }
        .details { line-height: 1.45; margin-top: 12px; }
        .details p { margin: 2px 0; }
        .meta { margin-top: 12px; width: 100%; }
        .meta td { padding: 3px 0; vertical-align: top; }
        table.items { border-collapse: collapse; margin-top: 18px; width: 100%; }
        .items th { background: #f3f4f6; border-bottom: 1px solid #d1d5db; font-weight: 700; padding: 8px; text-align: left; }
        .items td { border-bottom: 1px solid #e5e7eb; padding: 8px; }
        .right { text-align: right; }
        .totals { margin-left: auto; margin-top: 18px; width: 280px; }
        .totals td { padding: 5px 0; }
        .grand-total { border-top: 2px solid #111827; font-size: 15px; font-weight: 700; padding-top: 8px; }
        .footer { border-top: 1px solid #d1d5db; color: #6b7280; margin-top: 30px; padding-top: 12px; text-align: center; }
    </style>
</head>
<body>
    @php($currency = $receipt['currency'] ?? 'OMR')

    <div class="header">
        <div class="brand">Omani Industrial Gas ERP</div>
        <div class="muted">Quick Sale Receipt</div>

        <table class="meta">
            <tr>
                <td><strong>Receipt:</strong> {{ $receipt['number'] }}</td>
                <td class="right"><strong>Invoice:</strong> {{ $receipt['invoice_number'] }}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong> {{ $receipt['sale_date'] ?? $receipt['date'] }}</td>
                <td class="right"><strong>Payment:</strong> {{ $receipt['payment_method'] }}</td>
            </tr>
        </table>

        <div class="details">
            <p><strong>Customer:</strong> {{ $receipt['customer_name'] }}</p>
            @if(! empty($receipt['customer_phone']))
                <p><strong>Phone:</strong> {{ $receipt['customer_phone'] }}</p>
            @endif
            @if(! empty($receipt['vat_trn']))
                <p><strong>TRN:</strong> {{ $receipt['vat_trn'] }}</p>
            @endif
            @if(! empty($receipt['customer_address']))
                <p><strong>Address:</strong> {{ $receipt['customer_address'] }}</p>
            @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">VAT</th>
                <th class="right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt['items'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="right">{{ number_format((float) $item['quantity'], 3) }}</td>
                    <td class="right">{{ $currency }} {{ number_format((float) $item['unit_price'], 3) }}</td>
                    <td class="right">{{ $currency }} {{ number_format((float) $item['tax_amount'], 3) }}</td>
                    <td class="right">{{ $currency }} {{ number_format((float) $item['line_total'], 3) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ $currency }} {{ number_format((float) $receipt['subtotal'], 3) }}</td>
        </tr>
        <tr>
            <td>VAT</td>
            <td class="right">{{ $currency }} {{ number_format((float) $receipt['tax_amount'], 3) }}</td>
        </tr>
        <tr>
            <td class="grand-total">Total</td>
            <td class="right grand-total">{{ $currency }} {{ number_format((float) $receipt['total_amount'], 3) }}</td>
        </tr>
    </table>

    <div class="footer">Thank you for your business.</div>
</body>
</html>
