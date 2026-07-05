<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\PermissionRegistrar;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        $permissionIds = RoleResource::selectedPermissionIdsFromData($this->data);

        $this->record->permissions()->sync($permissionIds);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}