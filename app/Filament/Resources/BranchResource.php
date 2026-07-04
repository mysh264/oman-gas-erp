<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = "heroicon-o-building-office";

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make("name")->required()->maxLength(255),
            Forms\Components\TextInput::make("location")->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make("name")->searchable()->sortable(),
            Tables\Columns\TextColumn::make("location")->searchable()->sortable(),
        ])->filters([])->actions([
            Tables\Actions\EditAction::make(),
        ])->bulkActions([
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
        return auth()->user()?->hasRole("Manager") ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole("Manager") ?? false;
    }

    public static function canEdit(mixed $record): bool
    {
        return auth()->user()?->hasRole("Manager") ?? false;
    }

    public static function canDelete(mixed $record): bool
    {
        return auth()->user()?->hasRole("Manager") ?? false;
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListBranches::route("/"),
            "create" => Pages\CreateBranch::route("/create"),
            "edit" => Pages\EditBranch::route("/{record}/edit"),
        ];
    }
}
