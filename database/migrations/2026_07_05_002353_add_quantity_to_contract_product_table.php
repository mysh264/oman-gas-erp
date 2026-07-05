<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_product', function (Blueprint $table) {
            if (! Schema::hasColumn('contract_product', 'quantity')) {
                $table->decimal('quantity', 12, 3)->default(1)->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contract_product', function (Blueprint $table) {
            if (Schema::hasColumn('contract_product', 'quantity')) {
                $table->dropColumn('quantity');
            }
        });
    }
};
