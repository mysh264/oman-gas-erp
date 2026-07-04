<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'contract_id')) {
                $table->foreignId('contract_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained('contracts')
                    ->nullOnDelete();

                $table->index('contract_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'contract_id')) {
                $table->foreignId('contract_id')
                    ->nullable()
                    ->after('invoice_id')
                    ->constrained('contracts')
                    ->nullOnDelete();

                $table->index('contract_id');
            }
        });

        if (! Schema::hasTable('contract_product')) {
            Schema::create('contract_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['contract_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_product');

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'contract_id')) {
                $table->dropConstrainedForeignId('contract_id');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'contract_id')) {
                $table->dropConstrainedForeignId('contract_id');
            }
        });
    }
};
