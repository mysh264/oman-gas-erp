<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Pages\Page;

class DailyHandoff extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Daily Handoff';
    protected static ?string $title = 'Daily Handoff';
    protected static string $view = 'filament.pages.daily-handoff';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Manager') ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'orders' => Order::query()->whereDate('created_at', today())->latest()->get(),
            'payments' => Payment::query()->whereDate('created_at', today())->latest()->get(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Print PDF')
                ->label('Print PDF')
                ->icon('heroicon-o-printer')
                ->url(route('pdf.handoff'))
                ->openUrlInNewTab(),
        ];
    }
}