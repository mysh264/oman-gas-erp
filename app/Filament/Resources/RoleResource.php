<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Roles';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->unique(ignoreRecord: true)
                ->label('Role Name')
                ->columnSpanFull(),
            Forms\Components\Grid::make(4)
                ->columnSpanFull()
                ->schema([
                    Forms\Components\Section::make('Resource Permissions')
                        ->columnSpan(2)
                        ->schema([
                            Forms\Components\Tabs::make('Resources')
                                ->tabs(
                                    collect(static::permissionResources())
                                        ->map(fn (string $resource) => Forms\Components\Tabs\Tab::make(ucfirst($resource))
                                            ->schema([
                                                Forms\Components\CheckboxList::make("permission_groups.{$resource}")
                                                    ->options(static::permissionOptionsFor($resource))
                                                    ->columns(2)
                                                    ->bulkToggleable()
                                                    ->dehydrated(false)
                                                    ->label('')
                                                    ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?Role $record) use ($resource): void {
                                                        if (! $record?->exists) {
                                                            return;
                                                        }

                                                        $component->state(
                                                            $record->permissions()
                                                                ->where('name', 'like', "%_{$resource}")
                                                                ->pluck('permissions.id')
                                                                ->map(fn ($id): string => (string) $id)
                                                                ->all()
                                                        );
                                                    }),
                                            ]))
                                        ->toArray()
                                ),
                        ]),
                    Forms\Components\Section::make('Global Privileges')
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\CheckboxList::make('permission_groups.global')
                                ->options(static::systemPermissionOptions())
                                ->columns(1)
                                ->bulkToggleable()
                                ->dehydrated(false)
                                ->label('Danger Zone')
                                ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?Role $record): void {
                                    if (! $record?->exists) {
                                        return;
                                    }

                                    $component->state(
                                        $record->permissions()
                                            ->whereIn('name', static::systemPermissionNames())
                                            ->pluck('permissions.id')
                                            ->map(fn ($id): string => (string) $id)
                                            ->all()
                                    );
                                }),
                        ]),
                    Forms\Components\Section::make('Audit')
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\CheckboxList::make('permission_groups.audit')
                                ->options(static::auditPermissionOptions())
                                ->columns(1)
                                ->bulkToggleable()
                                ->dehydrated(false)
                                ->label('Audit Access')
                                ->afterStateHydrated(function (Forms\Components\CheckboxList $component, ?Role $record): void {
                                    if (! $record?->exists) {
                                        return;
                                    }

                                    $component->state(
                                        $record->permissions()
                                            ->whereIn('name', static::auditPermissionNames())
                                            ->pluck('permissions.id')
                                            ->map(fn ($id): string => (string) $id)
                                            ->all()
                                    );
                                }),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Role Name'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions Count')
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

    public static function selectedPermissionIdsFromData(array $data): array
    {
        return collect($data['permission_groups'] ?? [])
            ->flatten()
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public static function permissionResources(): array
    {
        return ['order', 'client', 'product', 'contract', 'invoice', 'payment', 'user', 'role'];
    }

    protected static function permissionOptionsFor(string $resource): array
    {
        return Permission::query()
            ->where('name', 'like', "%_{$resource}")
            ->orderByRaw(
                'case
                    when name = ? then 1
                    when name = ? then 2
                    when name = ? then 3
                    when name = ? then 4
                    when name = ? then 5
                    else 6
                end',
                [
                    "list_access_{$resource}",
                    "open_details_{$resource}",
                    "create_{$resource}",
                    "update_{$resource}",
                    "delete_{$resource}",
                ]
            )
            ->get()
            ->mapWithKeys(fn (Permission $permission): array => [
                $permission->id => static::permissionActionLabel($permission->name, $resource),
            ])
            ->all();
    }

    protected static function systemPermissionOptions(): array
    {
        return Permission::query()
            ->whereIn('name', static::systemPermissionNames())
            ->get()
            ->mapWithKeys(fn (Permission $permission): array => [
                $permission->id => 'Enable Global Management (Can see all data)',
            ])
            ->all();
    }

    protected static function auditPermissionOptions(): array
    {
        return Permission::query()
            ->whereIn('name', static::auditPermissionNames())
            ->get()
            ->mapWithKeys(fn (Permission $permission): array => [
                $permission->id => 'View Audit Logs',
            ])
            ->all();
    }

    protected static function systemPermissionNames(): array
    {
        return ['manage_all_resources'];
    }

    protected static function auditPermissionNames(): array
    {
        return ['view_audit_logs_audit_log'];
    }

    protected static function permissionActionLabel(string $permission, string $resource): string
    {
        if (str_starts_with($permission, 'list_access_')) {
            return 'List Access';
        }

        if (str_starts_with($permission, 'open_details_')) {
            return 'Open Details';
        }

        $action = str($permission)
            ->beforeLast("_{$resource}")
            ->replace('_', ' ')
            ->toString();

        return ucwords($action);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('list_access_role') ?? false;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return auth()->user()?->can('open_details_role') ?? false;
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
