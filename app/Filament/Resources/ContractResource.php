<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Models\Client;
use App\Models\Contract;
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
                Forms\Components\Section::make('Gas Supply Details')
                    ->schema([
                        Forms\Components\CheckboxList::make('products')
                            ->relationship('products', 'name')
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
            ->filters([
                //
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
