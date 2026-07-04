<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("contracts", function (Blueprint $table) {
            if (! Schema::hasColumn("contracts", "total_value")) {
                $table->decimal("total_value", 12, 3)->default(0)->after("status");
            }

            if (! Schema::hasColumn("contracts", "description")) {
                $table->text("description")->nullable()->after("total_value");
            }
        });
    }

    public function down(): void
    {
        Schema::table("contracts", function (Blueprint $table) {
            if (Schema::hasColumn("contracts", "description")) {
                $table->dropColumn("description");
            }

            if (Schema::hasColumn("contracts", "total_value")) {
                $table->dropColumn("total_value");
            }
        });
    }
};
