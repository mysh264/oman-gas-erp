<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingTasks extends TableWidget
{
    protected static ?string $heading = 'Pending Tasks';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Order::query()
            ->where('user_id', auth()->id())
            ->where('status', 'pending');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Order ID'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No pending tasks')
            ->emptyStateDescription('You do not have any pending orders assigned to you.');
    }
}
