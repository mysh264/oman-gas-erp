<x-filament-panels::page>
    <div class="space-y-6">
        <section>
            <h2 class="text-lg font-semibold">Today's Orders</h2>
            <table class="mt-3 w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th class="border px-3 py-2 text-left">Order</th>
                        <th class="border px-3 py-2 text-left">Client</th>
                        <th class="border px-3 py-2 text-left">Date</th>
                        <th class="border px-3 py-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="border px-3 py-2">#{{ $order->id }}</td>
                            <td class="border px-3 py-2">{{ $order->client?->name ?? 'N/A' }}</td>
                            <td class="border px-3 py-2">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="border px-3 py-2 text-right">OMR {{ number_format((float) $order->total_amount, 3) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="border px-3 py-3 text-center" colspan="4">No orders today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section>
            <h2 class="text-lg font-semibold">Today's Payments</h2>
            <table class="mt-3 w-full border-collapse text-sm">
                <thead>
                    <tr>
                        <th class="border px-3 py-2 text-left">Payment</th>
                        <th class="border px-3 py-2 text-left">Invoice</th>
                        <th class="border px-3 py-2 text-left">Method</th>
                        <th class="border px-3 py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="border px-3 py-2">#{{ $payment->id }}</td>
                            <td class="border px-3 py-2">{{ $payment->invoice?->invoice_number ?? 'N/A' }}</td>
                            <td class="border px-3 py-2">{{ $payment->payment_method }}</td>
                            <td class="border px-3 py-2 text-right">OMR {{ number_format((float) $payment->amount, 3) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="border px-3 py-3 text-center" colspan="4">No payments today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </div>
</x-filament-panels::page>