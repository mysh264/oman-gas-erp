<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("payments", function (Blueprint $table) {
            if (! Schema::hasColumn("payments", "contract_id")) {
                $table->foreignId("contract_id")
                    ->nullable()
                    ->after("invoice_id")
                    ->constrained("contracts")
                    ->nullOnDelete();

                $table->index("contract_id");
            }
        });
    }

    public function down(): void
    {
        Schema::table("payments", function (Blueprint $table) {
            if (Schema::hasColumn("payments", "contract_id")) {
                $table->dropConstrainedForeignId("contract_id");
            }
        });
    }
};
