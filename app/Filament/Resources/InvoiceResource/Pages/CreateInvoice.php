<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function afterCreate(): void
    {
        $summary = InvoiceResource::calculateInvoiceSummary(
            $this->record->items()->get()->map(fn ($item): array => ['line_total' => $item->line_total])->all()
        );

        $this->record->update($summary);
    }
}