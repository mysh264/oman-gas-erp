<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("clients", function (Blueprint $table) {
            $table->string("phone_mobile")->nullable()->after("phone");
            $table->string("phone_landline")->nullable()->after("phone_mobile");
        });
    }

    public function down(): void
    {
        Schema::table("clients", function (Blueprint $table) {
            $table->dropColumn(["phone_mobile", "phone_landline"]);
        });
    }
};
