<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $clientQuery = $this->scopeByOwnership(Client::query(), 'user_id');
        $invoiceQuery = $this->scopeByOwnership(Invoice::query(), 'user_id');
        $expenseQuery = $this->scopeByOwnership(Expense::query(), 'created_by');

        $currentMonthExpenses = $expenseQuery
            ->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');

        return [
            Stat::make('Total Clients', $clientQuery->count()),
            Stat::make('Total Pending Invoices', $invoiceQuery->whereIn('status', ['Draft', 'Unpaid', 'Overdue'])->count()),
            Stat::make('Total Expenses (Current Month)', 'OMR '.number_format((float) $currentMonthExpenses, 3)),
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
