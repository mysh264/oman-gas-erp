<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'description',
        'default_price',
        'tax_rate',
        'unit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:3',
            'tax_rate' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function contractItems(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
