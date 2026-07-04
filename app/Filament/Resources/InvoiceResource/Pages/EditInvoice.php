<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('printPdf')
                ->label('Print PDF')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('pdf.invoice', $this->record))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $summary = InvoiceResource::calculateInvoiceSummary(
            $this->record->items()->get()->map(fn ($item): array => ['line_total' => $item->line_total])->all()
        );

        $this->record->update($summary);
    }
}