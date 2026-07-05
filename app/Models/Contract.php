<?php

namespace App\Models;

use App\Models\Concerns\AssignsCurrentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Contract extends Model
{
    use LogsActivity;
    use AssignsCurrentUser;

    protected $fillable = [
        'user_id',
        'custom_id',
        'client_id',
        'start_date',
        'end_date',
        'total_value',
        'status',
        'description',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Contract $contract): void {
            $contract->created_by = auth()->id();
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_value' => 'decimal:3',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Contract $contract): void {
            if (filled($contract->custom_id)) {
                return;
            }

            $date = now()->format('Y-m-d');
            $count = static::query()->whereDate('created_at', now())->count() + 1;

            $contract->custom_id = "GAS-{$date}-{$count}";
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function getLabelAttribute(): string
    {
        $identifier = $this->custom_id ?? "Contract #{$this->id}";

        return "{$identifier} - " . ($this->client ? $this->client->name : 'No Client');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity')->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
