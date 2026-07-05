<?php

namespace App\Models;

use App\Models\Concerns\AssignsCurrentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use AssignsCurrentUser;
    protected $fillable = [
        "user_id",
        "name",
        "cr_number",
        "vat_number",
        "commercial_registration_number",
        "country",
        "city",
        "address",
        "phone",
        "phone_mobile",
        "phone_landline",
        "email",
        "is_active",
    ];

    protected function casts(): array
    {
        return [
            "is_active" => "boolean",
        ];
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
