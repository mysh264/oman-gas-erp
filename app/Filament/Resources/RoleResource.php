<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
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
                Section::make("Permissions Matrix")
                    ->schema([
                        Grid::make(1)
                            ->schema(static::permissionMatrixSchema()),
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

    public static function permissionMatrixSchema(): array
    {
        $fieldsets = collect(static::permissionResources())
            ->map(fn (string $resource): Fieldset => Fieldset::make(ucfirst($resource) . " Permissions")
                ->schema([
                    CheckboxList::make("permission_matrix.{$resource}")
                        ->options(static::permissionOptionsFor($resource))
                        ->columns(4)
                        ->bulkToggleable()
                        ->dehydrated(false)
                        ->label("")
                        ->afterStateHydrated(function (CheckboxList $component, ?Role $record) use ($resource): void {
                            if (! $record?->exists) {
                                return;
                            }

                            $component->state(
                                $record->permissions()
                                    ->where("name", "like", "%_{$resource}")
                                    ->pluck("permissions.id")
                                    ->map(fn ($id): string => (string) $id)
                                    ->all()
                            );
                        }),
                ]))
            ->all();

        return [
            Fieldset::make("System Permissions")
                ->schema([
                    CheckboxList::make("permission_matrix.system")
                        ->options(static::systemPermissionOptions())
                        ->columns(4)
                        ->bulkToggleable()
                        ->dehydrated(false)
                        ->label("")
                        ->afterStateHydrated(function (CheckboxList $component, ?Role $record): void {
                            if (! $record?->exists) {
                                return;
                            }

                            $component->state(
                                $record->permissions()
                                    ->whereIn("name", static::systemPermissionNames())
                                    ->pluck("permissions.id")
                                    ->map(fn ($id): string => (string) $id)
                                    ->all()
                            );
                        }),
                ]),
            ...$fieldsets,
        ];
    }

    public static function selectedPermissionIdsFromData(array $data): array
    {
        return collect($data["permission_matrix"] ?? [])
            ->flatten()
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public static function permissionResources(): array
    {
        return ["order", "client", "product", "contract", "invoice", "payment", "user", "role"];
    }

    protected static function permissionOptionsFor(string $resource): array
    {
        return Permission::query()
            ->where("name", "like", "%_{$resource}")
            ->orderByRaw("case
                when name = ? then 1
                when name = ? then 2
                when name = ? then 3
                when name = ? then 4
                when name = ? then 5
                else 6
            end", [
                "view_any_{$resource}",
                "view_{$resource}",
                "create_{$resource}",
                "update_{$resource}",
                "delete_{$resource}",
            ])
            ->pluck("name", "id")
            ->map(fn (string $name): string => static::permissionActionLabel($name, $resource))
            ->all();
    }

    protected static function systemPermissionOptions(): array
    {
        return Permission::query()
            ->whereIn("name", static::systemPermissionNames())
            ->pluck("name", "id")
            ->map(fn (string $name): string => ucwords(str_replace("_", " ", $name)))
            ->all();
    }

    protected static function systemPermissionNames(): array
    {
        return ["manage_all_resources"];
    }

    protected static function permissionActionLabel(string $permission, string $resource): string
    {
        $action = str($permission)->beforeLast("_{$resource}")->replace("_", " ")->toString();

        return ucwords($action);
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