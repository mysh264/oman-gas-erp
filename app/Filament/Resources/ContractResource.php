<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->options(fn (): array => Client::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('custom_id')
                    ->label('Contract Reference ID')
                    ->default(function (): string {
                        $date = now()->format('Y-m-d');
                        $count = Contract::query()->whereDate('created_at', now())->count() + 1;

                        return "GAS-{$date}-{$count}";
                    })
                    ->placeholder('e.g., GAS-2026-001')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'This Contract ID already exists.',
                    ])
                    ->readOnly(),
                Forms\Components\Section::make('Gas Supply Details')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Products')
                            ->relationship('items')
                            ->defaultItems(0)
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                        $product = Product::query()->find($state);

                                        $set('unit_price', $product?->default_price ?? 0);
                                        $set('subtotal', ((float) ($product?->default_price ?? 0)));
                                    }),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                        $quantity = (float) ($get('quantity') ?? 0);
                                        $unitPrice = (float) ($get('unit_price') ?? 0);

                                        $set('subtotal', $quantity * $unitPrice);
                                    }),
                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->prefix('OMR')
                                    ->required(),
                                Forms\Components\TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('OMR')
                                    ->required(),
                            ])
                            ->columns(2),
                    ]),
                Forms\Components\DatePicker::make('start_date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d'),
                Forms\Components\TextInput::make('total_value')
                    ->numeric()
                    ->prefix('OMR')
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->visible(fn () => auth()->user()?->can('view_creator_info') ?? false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('custom_id')
                    ->label('Contract ID')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date('d/m/Y')
                    ->badge()
                    ->color(function (Contract $record): string {
                        if (! $record->end_date) {
                            return 'gray';
                        }

                        $daysRemaining = now()->startOfDay()->diffInDays($record->end_date->startOfDay(), false);

                        return match (true) {
                            $daysRemaining < 30 => 'danger',
                            $daysRemaining < 60 => 'warning',
                            default => 'success',
                        };
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')->money('OMR')->sortable(),
                Tables\Columns\TextColumn::make('payments_sum_amount')->sum('payments', 'amount')->label('Received'),
                Tables\Columns\TextColumn::make('balance')->state(fn (Contract $record) => (float) $record->total_value - (float) $record->payments()->sum('amount'))->money('OMR')->label('Balance'),
            ])
            ->filters([])
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
        return [];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('list_access_contract') ?? false;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('open_details_contract') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_contract') ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('update_contract') ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('delete_contract') ?? false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        if (! auth()->user()?->can('manage_all_resources')) {
            return $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
