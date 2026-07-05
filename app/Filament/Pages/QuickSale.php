<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Forms\Components;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

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
        $this->form->fill([
            'customer_name' => 'Walk-in Customer',
            'payment_method' => 'Cash',
            'items' => [
                ['quantity' => 1],
            ],
        ]);
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
                    Components\Section::make('Customer Information')
                        ->schema([
                            Components\TextInput::make('customer_name')
                                ->label('Name (or Walk-in)')
                                ->default('Walk-in Customer')
                                ->required()
                                ->maxLength(255),
                            Components\TextInput::make('customer_phone')
                                ->label('Phone Number')
                                ->tel()
                                ->prefix('+968')
                                ->maxLength(50),
                        ])
                        ->columnSpan(['default' => 3, 'lg' => 1]),

                    Components\Section::make('Products & Payment')
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
                                        ->prefix('OMR')
                                        ->required()
                                        ->readOnly(),
                                ])
                                ->columns(['default' => 1, 'md' => 3])
                                ->addActionLabel('Add Product')
                                ->required()
                                ->minItems(1),
                            Components\Select::make('payment_method')
                                ->options([
                                    'Cash' => 'Cash',
                                    'Card' => 'Card',
                                    'Bank Transfer' => 'Bank Transfer',
                                ])
                                ->default('Cash')
                                ->required(),
                        ])
                        ->columnSpan(['default' => 3, 'lg' => 2]),
                ]),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState();

        if (blank($data['items'] ?? [])) {
            Notification::make()
                ->title('Add at least one product')
                ->danger()
                ->send();

            return null;
        }

        return redirect()->route('quick-sale.process', [
            'data' => base64_encode(json_encode($data, JSON_THROW_ON_ERROR)),
        ]);
    }
}
