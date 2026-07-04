<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryTransactionResource\Pages;
use App\Filament\Resources\InventoryTransactionResource\RelationManagers;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InventoryTransactionResource extends Resource
{
    protected static ?string $model = InventoryTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->options(fn (): array => Product::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('warehouse_id')
                    ->options(fn (): array => Warehouse::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('transaction_type')
                    ->options([
                        'In' => 'In',
                        'Out' => 'Out',
                        'Adjustment' => 'Adjustment',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->rules([
                        function (Get $get, ?InventoryTransaction $record): \Closure {
                            return function (string $attribute, mixed $value, \Closure $fail) use ($get, $record): void {
                                if ($get('transaction_type') !== 'Out') {
                                    return;
                                }

                                $productId = $get('product_id');
                                $warehouseId = $get('warehouse_id');

                                if (! $productId || ! $warehouseId || ! is_numeric($value)) {
                                    return;
                                }

                                $stockQuery = InventoryTransaction::query()
                                    ->where('product_id', $productId)
                                    ->where('warehouse_id', $warehouseId);

                                if ($record?->exists) {
                                    $stockQuery->whereKeyNot($record->getKey());
                                }

                                $currentStock = $stockQuery->get()->sum(function (InventoryTransaction $transaction): int {
                                    return match ($transaction->transaction_type) {
                                        'In', 'Adjustment' => (int) $transaction->quantity,
                                        'Out' => -1 * (int) $transaction->quantity,
                                        default => 0,
                                    };
                                });

                                if ((int) $value > $currentStock) {
                                    $fail('Insufficient stock in this warehouse.');
                                }
                            };
                        },
                    ]),
                Forms\Components\TextInput::make('reference_number')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
        return auth()->user()?->hasAnyRole(['Manager', 'Warehouse Staff']) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['Manager', 'Warehouse Staff']) ?? false;
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
            'index' => Pages\ListInventoryTransactions::route('/'),
            'create' => Pages\CreateInventoryTransaction::route('/create'),
            'edit' => Pages\EditInventoryTransaction::route('/{record}/edit'),
        ];
    }
}
