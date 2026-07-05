<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = "heroicon-o-shield-check";

    protected static ?string $navigationLabel = "Roles";

    protected static ?string $modelLabel = "Role";

    protected static ?string $pluralModelLabel = "Roles";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make("name")
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label("Role Name"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name")
                    ->searchable()
                    ->sortable()
                    ->label("Role Name"),
                Tables\Columns\TextColumn::make("users_count")
                    ->counts("users")
                    ->label("Users")
                    ->sortable(),
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
        return auth()->user()?->hasAnyRole(["Admin", "Manager"]) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(["Admin", "Manager"]) ?? false;
    }

    public static function canEdit(mixed $record): bool
    {
        return auth()->user()?->hasAnyRole(["Admin", "Manager"]) ?? false;
    }

    public static function canDelete(mixed $record): bool
    {
        return auth()->user()?->hasAnyRole(["Admin", "Manager"]) ?? false;
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListRoles::route("/"),
            "create" => Pages\CreateRole::route("/create"),
            "edit" => Pages\EditRole::route("/{record}/edit"),
        ];
    }
}
