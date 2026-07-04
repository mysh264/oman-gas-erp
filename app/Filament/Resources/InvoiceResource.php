<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('client_id')
                    ->options(fn (): array => Client::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('invoice_date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->default(now())
                    ->required(),
                Forms\Components\Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->options(fn (): array => Product::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set, Get $get): void {
                                $price = Product::query()->find($state)?->default_price ?? 0;

                                $set('unit_price', number_format((float) $price, 3, '.', ''));
                                $set('line_total', self::calculateLineTotal($get));
                            }),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->step('0.001')
                            ->default(1)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => $set('line_total', self::calculateLineTotal($get))),
                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->step('0.001')
                            ->default(0)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get) => $set('line_total', self::calculateLineTotal($get))),
                        Forms\Components\TextInput::make('line_total')
                            ->numeric()
                            ->step('0.001')
                            ->readOnly()
                            ->dehydrated()
                            ->default(0),
                    ])
                    ->columns(4)
                    ->defaultItems(0)
                    ->live()
                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncInvoiceTotals($set, $get('items') ?? []))
                    ->afterStateHydrated(fn (Set $set, Get $get) => self::syncInvoiceTotals($set, $get('items') ?? []))
                    ->collapsible(),
                Forms\Components\TextInput::make('subtotal')
                    ->numeric()
                    ->step('0.001')
                    ->readOnly()
                    ->dehydrated()
                    ->default(0),
                Forms\Components\TextInput::make('vat_amount')
                    ->numeric()
                    ->step('0.01')
                    ->readOnly()
                    ->dehydrated()
                    ->default(0),
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->step('0.001')
                    ->readOnly()
                    ->dehydrated()
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Unpaid' => 'Unpaid',
                        'Paid' => 'Paid',
                        'Overdue' => 'Overdue',
                    ])
                    ->default('Draft')
                    ->required(),
            ]);
    }

    public static function calculateLineTotal(Get $get): string
    {
        return number_format((float) $get('quantity') * (float) $get('unit_price'), 3, '.', '');
    }

    public static function calculateInvoiceSummary(array $items): array
    {
        $subtotal = round(collect($items)->sum(fn (array $item): float => (float) ($item['line_total'] ?? 0)), 3);
        $vat = round($subtotal * 0.05, 3);
        $total = round($subtotal + $vat, 3);

        return [
            'subtotal' => number_format($subtotal, 3, '.', ''),
            'vat_amount' => number_format($vat, 3, '.', ''),
            'tax_amount' => number_format($vat, 3, '.', ''),
            'total_amount' => number_format($total, 3, '.', ''),
        ];
    }

    public static function syncInvoiceTotals(Set $set, array $items): void
    {
        $summary = self::calculateInvoiceSummary($items);

        $set('subtotal', $summary['subtotal']);
        $set('vat_amount', $summary['vat_amount']);
        $set('tax_amount', $summary['tax_amount']);
        $set('total_amount', $summary['total_amount']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('client.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('subtotal')->money('OMR', divideBy: 1)->sortable(),
                Tables\Columns\TextColumn::make('vat_amount')->money('OMR', divideBy: 1)->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->money('OMR', divideBy: 1)->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->label('Filter by Branch'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('Manager') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('Manager') ?? false;
    }

    public static function canEdit(mixed $record): bool
    {
        return auth()->user()?->hasRole('Manager') ?? false;
    }

    public static function canDelete(mixed $record): bool
    {
        return auth()->user()?->hasRole('Manager') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}