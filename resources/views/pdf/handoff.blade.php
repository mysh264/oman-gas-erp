<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Daily Handoff {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; margin: 0; padding: 24px; }
        h1, h2, p { margin: 0; }
        .header { margin-bottom: 20px; border-bottom: 1px solid #d1d5db; padding-bottom: 12px; }
        .section { margin-top: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Handoff</h1>
        <p>{{ now()->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <h2>Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Client</th>
                    <th>Status</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->client?->name ?? 'N/A' }}</td>
                        <td>{{ $order->status }}</td>
                        <td class="right">OMR {{ number_format((float) $order->total_amount, 3) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No orders today.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Payments</h2>
        <table>
            <thead>
                <tr>
                    <th>Payment</th>
                    <th>Invoice</th>
                    <th>Method</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>#{{ $payment->id }}</td>
                        <td>{{ $payment->invoice?->invoice_number ?? 'N/A' }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td class="right">OMR {{ number_format((float) $payment->amount, 3) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No payments today.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>