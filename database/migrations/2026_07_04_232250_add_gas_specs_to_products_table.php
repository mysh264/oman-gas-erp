<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'gas_type')) {
                $table->string('gas_type')->nullable()->after('name');
            }

            if (! Schema::hasColumn('products', 'capacity')) {
                $table->decimal('capacity', 10, 2)->nullable()->after('gas_type');
            }

            if (! Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->default('kg')->after('capacity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'unit')) {
                $table->dropColumn('unit');
            }

            if (Schema::hasColumn('products', 'capacity')) {
                $table->dropColumn('capacity');
            }

            if (Schema::hasColumn('products', 'gas_type')) {
                $table->dropColumn('gas_type');
            }
        });
    }
};
