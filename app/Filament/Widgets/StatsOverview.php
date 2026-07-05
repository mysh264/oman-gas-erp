<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $invoiceQuery = $this->scopeByOwnership(Invoice::query(), 'user_id');
        $productQuery = $this->scopeByOwnership(Product::query(), 'created_by');

        return [
            Stat::make('Total Sales', 'OMR ' . number_format((float) $invoiceQuery->sum('total_amount'), 3)),
            Stat::make('Pending Invoices', $invoiceQuery->where('status', 'pending')->count()),
            Stat::make('Low Stock Items', $productQuery->where('stock_quantity', '<', 10)->count()),
        ];
    }

    protected function scopeByOwnership(Builder $query, string $column): Builder
    {
        if (! auth()->user()?->can('manage_all_resources')) {
            $query->where($column, auth()->id());
        }

        return $query;
    }
}
