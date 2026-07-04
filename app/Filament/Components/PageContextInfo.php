<?php

namespace App\Filament\Components;

class PageContextInfo
{
    public function getContextDescription(): string
    {
        return match (request()->segment(2)) {
            'orders' => 'Orders: Track daily gas deliveries and link them to active client contracts.',
            'clients' => 'Clients: Manage customer profiles, CR numbers, and view contract history.',
            'contracts' => 'Contracts: Manage long-term supply agreements, products, and financial totals.',
            'invoices' => 'Invoices: Generate billable documents for completed orders.',
            default => 'Navigate and manage your gas supply operations efficiently.',
        };
    }
}
