<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('atsauksmes', function (Blueprint $table) {
            $table->integer('vertejums')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atsauksmes', function (Blueprint $table) {
            $table->integer('vertejums')->nullable(false)->change();
        });
    }
};
