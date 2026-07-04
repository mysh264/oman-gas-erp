<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Sales', 'OMR ' . number_format((float) Invoice::sum('total_amount'), 3)),
            Stat::make('Pending Invoices', Invoice::where('status', 'pending')->count()),
            Stat::make('Low Stock Items', Product::where('stock_quantity', '<', 10)->count()),
        ];
    }
}
