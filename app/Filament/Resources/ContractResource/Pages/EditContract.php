<?php

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContract extends EditRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Print PDF')
                ->label('Print PDF')
                ->icon('heroicon-o-printer')
                ->url(fn ($record) => route('pdf.contract', $record), true)
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    public function getPdfFilename(): string
    {
        return ($this->record->custom_id ?? 'contract-'.$this->record->id).'.pdf';
    }
}
