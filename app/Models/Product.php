<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sku', 'name', 'description', 'default_price', 'tax_rate', 'unit', 'is_active'])]
class Product extends Model
{
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

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
