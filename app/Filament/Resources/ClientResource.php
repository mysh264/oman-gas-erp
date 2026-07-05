<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = "heroicon-o-rectangle-stack";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make("name")->required()->maxLength(255),
                Forms\Components\TextInput::make("cr_number")
                    ->label("CR Number (Oman)")
                    ->placeholder("e.g., 1234567")
                    ->maxLength(255),
                Forms\Components\TextInput::make("vat_number")->maxLength(255),
                Forms\Components\TextInput::make("city")->maxLength(255),
                Forms\Components\Section::make("Contact Information")
                    ->schema([
                        Forms\Components\TextInput::make("email")->email(),
                        Forms\Components\TextInput::make("phone_mobile")->tel()->prefix("+968"),
                        Forms\Components\TextInput::make("phone_landline")->tel()->prefix("+968"),
                    ])
                    ->columns(3),
                Forms\Components\Repeater::make("contacts")
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make("name")->required()->maxLength(255),
                        Forms\Components\TextInput::make("position")->maxLength(255),
                        Forms\Components\TextInput::make("phone")->tel()->maxLength(255),
                        Forms\Components\TextInput::make("email")->email()->maxLength(255),
                        Forms\Components\Toggle::make("is_primary")->default(false),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("cr_number")->searchable(),
                Tables\Columns\TextColumn::make("vat_number")->searchable(),
                Tables\Columns\TextColumn::make("city")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("phone_mobile")->label("Mobile")->searchable(),
                Tables\Columns\TextColumn::make("contracts_count")->counts("contracts")->label("Contracts"),
                Tables\Columns\TextColumn::make("invoices_sum_total_amount")->sum("invoices", "total_amount")->label("Total OMR"),
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
        return auth()->user()?->can('list_access_client') ?? false;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('open_details_client') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_client') ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('update_client') ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('delete_client') ?? false;
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
            "index" => Pages\ListClients::route("/"),
            "create" => Pages\CreateClient::route("/create"),
            "edit" => Pages\EditClient::route("/{record}/edit"),
        ];
    }
}
