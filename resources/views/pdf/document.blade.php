<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 0;
            padding: 24px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 16px;
        }
        .logo {
            width: 160px;
            height: 56px;
            border: 1px dashed #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 11px;
        }
        h1, h2, p {
            margin: 0;
        }
        .meta, .summary, .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        .meta td, .summary td, .summary th, .items td, .items th {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
        }
        .summary th, .items th {
            background: #f9fafb;
            text-align: left;
        }
        .right {
            text-align: right;
        }
        .section-title {
            margin-top: 24px;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="logo">Company Logo</div>
        </div>
        <div class="right">
            <h2>{{ $type }}</h2>
            <p>{{ $title }}</p>
        </div>
    </div>

    @if(isset($invoice))
        <table class="meta">
            <tr>
                <td>
                    <strong>Client</strong><br>
                    {{ $invoice->client?->name ?? 'N/A' }}
                </td>
                <td>
                    <strong>Invoice Date</strong><br>
                    {{ optional($invoice->invoice_date)->format('d/m/Y') ?? 'N/A' }}<br><br>
                    <strong>Due Date</strong><br>
                    {{ optional($invoice->due_date)->format('d/m/Y') ?? 'N/A' }}
                </td>
                <td>
                    <strong>Status</strong><br>
                    {{ $invoice->status ?? 'N/A' }}<br><br>
                    <strong>Total</strong><br>
                    OMR {{ number_format((float) $invoice->total_amount, 3) }}
                </td>
            </tr>
        </table>

        <div class="section-title">Invoice Items</div>
        <table class="items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="right">Qty</th>
                    <th class="right">Unit Price</th>
                    <th class="right">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'N/A' }}</td>
                        <td class="right">{{ number_format((float) $item->quantity, 3) }}</td>
                        <td class="right">OMR {{ number_format((float) $item->unit_price, 3) }}</td>
                        <td class="right">OMR {{ number_format((float) $item->line_total, 3) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <th>Subtotal</th>
                <td class="right">OMR {{ number_format((float) $invoice->subtotal, 3) }}</td>
            </tr>
            <tr>
                <th>Tax</th>
                <td class="right">OMR {{ number_format((float) $invoice->tax_amount, 3) }}</td>
            </tr>
            <tr>
                <th>Total</th>
                <td class="right">OMR {{ number_format((float) $invoice->total_amount, 3) }}</td>
            </tr>
        </table>
    @endif

    @if(isset($contract))
        <table class="meta">
            <tr>
                <td>
                    <strong>Client</strong><br>
                    {{ $contract->client?->name ?? 'N/A' }}
                </td>
                <td>
                    <strong>Start Date</strong><br>
                    {{ optional($contract->start_date)->format('d/m/Y') ?? 'N/A' }}<br><br>
                    <strong>End Date</strong><br>
                    {{ optional($contract->end_date)->format('d/m/Y') ?? 'N/A' }}
                </td>
                <td>
                    <strong>Status</strong><br>
                    {{ $contract->status ?? 'N/A' }}
                </td>
            </tr>
        </table>

        <div class="section-title">Contract Items</div>
        <table class="items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="right">Qty</th>
                    <th class="right">Unit Price</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contract->items as $item)
                    <tr>
                        <td>{{ $item->product?->name ?? 'N/A' }}</td>
                        <td class="right">{{ number_format((float) $item->quantity, 3) }}</td>
                        <td class="right">OMR {{ number_format((float) $item->unit_price, 3) }}</td>
                        <td class="right">OMR {{ number_format((float) $item->subtotal, 3) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>
