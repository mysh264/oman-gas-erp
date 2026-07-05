<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['orders', 'clients', 'products', 'contracts', 'invoices', 'payments'] as $tableName) {
            if (Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['orders', 'clients', 'products', 'contracts', 'invoices', 'payments'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'created_by')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('created_by');
            });
        }
    }
};
