<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\PermissionRegistrar;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        $this->record->permissions()->sync(RoleResource::selectedPermissionIdsFromData($this->data));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
