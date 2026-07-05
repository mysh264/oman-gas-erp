<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\PermissionRegistrar;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $permissionIds = RoleResource::selectedPermissionIdsFromData($this->data);

        $this->record->permissions()->sync($permissionIds);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}