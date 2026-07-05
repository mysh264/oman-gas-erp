<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(['sm' => 1, 'md' => 2])
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('contract_id')
                    ->relationship('contract', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->custom_id ?? "Contract #{$record->id}")
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\DatePicker::make('order_date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->format('Y-m-d')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'Draft' => 'Draft',
                        'Confirmed' => 'Confirmed',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('tax_amount')
                    ->numeric()
                    ->step('0.001')
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->step('0.001')
                    ->default(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('contract.custom_id')
                    ->label('Contract Ref')
                    ->default(fn ($record) => "Contract #{$record->contract_id}")
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('OMR'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'Completed',
                        'warning' => 'Confirmed',
                    ]),
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
        return auth()->user()?->can('list_access_order') ?? false;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('open_details_order') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_order') ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('update_order') ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('delete_order') ?? false;
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
