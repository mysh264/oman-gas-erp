<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Filament\Forms\Components;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuickSale extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Quick Sale (POS)';

    protected static ?string $title = 'New Quick Sale';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.quick-sale';

    public ?array $data = [];

    public function mount(): void
    {
        $this->resetQuickSaleForm();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->can('create_order') || $user?->hasAnyRole(['Admin', 'Sales', 'Sales Manager']));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Grid::make(3)->schema([
                    Components\Section::make('Customer Details')
                        ->schema([
                            Components\TextInput::make('customer_name')
                                ->required()
                                ->label('Customer Name')
                                ->default(fn () => $this->getDefaultCustomerName())
                                ->maxLength(255),
                            Components\TextInput::make('customer_phone')
                                ->label('Phone Number')
                                ->placeholder('+968 9000 0000')
                                ->maxLength(30),
                            Components\TextInput::make('vat_trn')
                                ->label('VAT / TRN Number')
                                ->placeholder('Enter TRN if business customer')
                                ->maxLength(255),
                            Components\Textarea::make('customer_address')
                                ->label('Address')
                                ->rows(2)
                                ->maxLength(1000),
                        ])
                        ->columnSpan(['default' => 3, 'lg' => 1]),

                    Components\Section::make('Products & Transaction Setup')
                        ->schema([
                            Components\Repeater::make('items')
                                ->schema([
                                    Components\Select::make('product_id')
                                        ->label('Product')
                                        ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('price', Product::find($state)?->default_price ?? '0.000')),
                                    Components\TextInput::make('quantity')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->required(),
                                    Components\TextInput::make('price')
                                        ->numeric()
                                        ->prefix(fn (Get $get) => $get('../../currency') ?: 'OMR')
                                        ->required()
                                        ->readOnly(),
                                ])
                                ->columns(['default' => 1, 'md' => 3])
                                ->addActionLabel('Add Product')
                                ->required()
                                ->minItems(1),
                            Components\Grid::make(3)->schema([
                                Components\DatePicker::make('sale_date')
                                    ->label('Transaction Date')
                                    ->default(today())
                                    ->displayFormat('d/m/Y')
                                    ->format('Y-m-d')
                                    ->required(),
                                Components\Select::make('currency')
                                    ->options([
                                        'OMR' => 'OMR',
                                        'AED' => 'AED',
                                        'USD' => 'USD',
                                    ])
                                    ->default('OMR')
                                    ->required(),
                                Components\Select::make('payment_method')
                                    ->options([
                                        'Cash' => 'Cash',
                                        'Card' => 'Card',
                                        'Bank Transfer' => 'Bank Transfer',
                                    ])
                                    ->default('Cash')
                                    ->required(),
                            ]),
                            Components\TextInput::make('vat_rate')
                                ->numeric()
                                ->default(5)
                                ->label('VAT Rate (%)')
                                ->required(),
                        ])
                        ->columnSpan(['default' => 3, 'lg' => 2]),
                ]),
            ])
            ->statePath('data');
    }

    public function processOnly(): void
    {
        $this->createQuickSale();

        Notification::make()
            ->title('Sale Processed Successfully!')
            ->success()
            ->send();

        $this->resetQuickSaleForm();
    }

    public function processAndPrint()
    {
        $sale = $this->createQuickSale();

        return redirect()->route('quick-sale.receipt', ['invoice' => $sale['invoice']->id]);
    }

    private function resetQuickSaleForm(): void
    {
        $this->form->fill([
            'customer_name' => $this->getDefaultCustomerName(),
            'customer_phone' => null,
            'vat_trn' => null,
            'customer_address' => null,
            'sale_date' => today()->format('Y-m-d'),
            'currency' => 'OMR',
            'payment_method' => 'Cash',
            'vat_rate' => 5,
            'items' => [
                ['quantity' => 1],
            ],
        ]);
    }

    private function getDefaultCustomerName(): string
    {
        $salesperson = auth()->user()?->name ?? 'Sales';
        $count = Order::query()
            ->where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->count() + 1;

        return $salesperson.' Customer '.str_pad((string) $count, 2, '0', STR_PAD_LEFT);
    }

    private function createQuickSale(): array
    {
        $data = $this->form->getState();
        $items = collect($data['items'] ?? [])->filter(fn (array $item) => filled($item['product_id'] ?? null));

        if ($items->isEmpty()) {
            Notification::make()
                ->title('Add at least one product')
                ->danger()
                ->send();

            throw ValidationException::withMessages([
                'data.items' => 'Add at least one product.',
            ]);
        }

        return DB::transaction(function () use ($data, $items): array {
            $customerName = trim((string) ($data['customer_name'] ?? '')) ?: $this->getDefaultCustomerName();
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

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = max((float) ($item['quantity'] ?? 1), 0.001);
                $unitPrice = (float) ($item['price'] ?? $product->default_price ?? 0);
                $lineSubtotal = round($quantity * $unitPrice, 3);

                $subtotal += $lineSubtotal;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_subtotal' => $lineSubtotal,
                ];
            }

            $subtotal = round($subtotal, 3);
            $taxAmount = round($subtotal * ($vatRate / 100), 3);
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
                $lineTax = round($line['line_subtotal'] * ($vatRate / 100), 3);
                $lineTotal = round($line['line_subtotal'] + $lineTax, 3);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'quantity' => number_format($line['quantity'], 3, '.', ''),
                    'unit_price' => number_format($line['unit_price'], 3, '.', ''),
                    'total_price' => number_format($lineTotal, 3, '.', ''),
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $line['product']->id,
                    'quantity' => number_format($line['quantity'], 3, '.', ''),
                    'unit_price' => number_format($line['unit_price'], 3, '.', ''),
                    'line_total' => number_format($lineTotal, 3, '.', ''),
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
                'client' => $client,
                'order' => $order,
                'invoice' => $invoice,
            ];
        });
    }
}
