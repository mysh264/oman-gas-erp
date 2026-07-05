<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'receipt_attachment')) {
                $table->string('receipt_attachment')->nullable()->after('reference_number');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'receipt_attachment')) {
                $table->string('receipt_attachment')->nullable()->after('total_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'receipt_attachment')) {
                $table->dropColumn('receipt_attachment');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'receipt_attachment')) {
                $table->dropColumn('receipt_attachment');
            }
        });
    }
};
