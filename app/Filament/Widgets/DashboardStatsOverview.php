<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $currentMonthExpenses = Expense::query()
            ->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        return [
            Stat::make('Total Clients', Client::query()->count()),
            Stat::make('Total Pending Invoices', Invoice::query()->whereIn('status', ['Draft', 'Unpaid', 'Overdue'])->count()),
            Stat::make('Total Expenses (Current Month)', 'OMR '.number_format((float) $currentMonthExpenses, 3)),
        ];
    }
}
