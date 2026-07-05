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
                Forms\Components\Section::make("Resource Permissions")
                    ->schema([
                        Forms\Components\CheckboxList::make("permissions")
                            ->relationship("permissions", "name")
                            ->getOptionLabelFromRecordUsing(fn ($record) => ucwords(str_replace("_", " ", $record->name)))
                            ->columns(4)
                            ->bulkToggleable()
                            ->label(""),
                    ]),
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
                Tables\Columns\TextColumn::make("permissions_count")
                    ->counts("permissions")
                    ->label("Permissions Count")
                    ->badge(),
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
        return auth()->user()?->can('view_any_role') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_role') ?? false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('update_role') ?? false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('delete_role') ?? false;
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
