<?php

namespace App\Models;

use App\Models\Concerns\AssignsCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['user_id', 'client_id', 'contract_id', 'order_date', 'status', 'tax_amount', 'total_amount', 'created_by'])]
class Order extends Model
{
    use LogsActivity;
    use AssignsCurrentUser;

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->created_by = auth()->id();
        });
    }

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'tax_amount' => 'decimal:3',
            'total_amount' => 'decimal:3',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
