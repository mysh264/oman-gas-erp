<?php

namespace App\Models;

use App\Models\Concerns\AssignsCurrentUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(["user_id", "invoice_id", "contract_id", "client_id", "amount", "payment_date", "payment_method", "reference_number", "receipt_image", "created_by"])]
class Payment extends Model
{
    use LogsActivity;
    use AssignsCurrentUser;

    protected static function booted(): void
    {
        static::creating(function (Payment $payment): void {
            $payment->created_by = auth()->id();
        });
    }

    protected function casts(): array
    {
        return [
            "amount" => "decimal:3",
            "payment_date" => "date",
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
