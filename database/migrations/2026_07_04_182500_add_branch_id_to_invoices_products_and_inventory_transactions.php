<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('invoice_number')->constrained('branches')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('description')->constrained('branches')->nullOnDelete();
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('warehouse_id')->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
