<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = "heroicon-o-rectangle-stack";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make("invoice_id")
                    ->relationship("invoice", "invoice_number")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make("client_id")
                    ->relationship("client", "name")
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make("amount")
                    ->numeric()
                    ->step("0.001")
                    ->prefix("OMR")
                    ->required(),
                Forms\Components\DatePicker::make("payment_date")
                    ->native(false)
                    ->displayFormat("d/m/Y")
                    ->format("Y-m-d")
                    ->required(),
                Forms\Components\Select::make("payment_method")
                    ->options([
                        "Cash" => "Cash",
                        "Bank Transfer" => "Bank Transfer",
                        "Check" => "Check",
                        "Credit" => "Credit",
                    ])
                    ->required()
                    ->live(),
                Forms\Components\FileUpload::make("receipt_image")
                    ->image()
                    ->directory("receipts")
                    ->visible(fn (Get $get) => $get("payment_method") === "Bank Transfer"),
                Forms\Components\TextInput::make("reference_number")
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("invoice.invoice_number")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("client.name")->searchable()->sortable(),
                Tables\Columns\TextColumn::make("amount")->money("OMR", divideBy: 1)->sortable(),
                Tables\Columns\TextColumn::make("payment_date")->date("d/m/Y")->sortable(),
                Tables\Columns\TextColumn::make("payment_method")->badge()->sortable(),
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
        return auth()->user()?->hasAnyRole(["Manager", "Sales Rep"]) ?? false;
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListPayments::route("/"),
            "create" => Pages\CreatePayment::route("/create"),
            "edit" => Pages\EditPayment::route("/{record}/edit"),
        ];
    }
}
