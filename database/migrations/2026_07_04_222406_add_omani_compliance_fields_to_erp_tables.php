<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('cr_number')->nullable()->after('name');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('vat_amount', 10, 2)->default(0.00)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('vat_amount');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('cr_number');
        });
    }
};