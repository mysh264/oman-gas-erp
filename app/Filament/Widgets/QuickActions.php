<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuickActions extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('', '')->extraAttributes(['class' => 'hidden']),
            Stat::make('Create New', '')
                ->description('Click to add')
                ->icon('heroicon-m-plus-circle')
                ->url('/admin/orders/create'),
            Stat::make('Create Contract', '')
                ->description('Click to add')
                ->icon('heroicon-m-document-plus')
                ->url('/admin/contracts/create'),
        ];
    }
}
