<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Product extends Model
{
    use LogsActivity;

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
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            $product->created_by = auth()->id();
        });
    }

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
