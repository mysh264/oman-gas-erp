<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'gas_type',
        'capacity',
        'description',
        'branch_id',
        'default_price',
        'tax_rate',
        'unit',
        'is_active',
        'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
            'default_price' => 'decimal:3',
            'tax_rate' => 'decimal:3',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function contractItems(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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
