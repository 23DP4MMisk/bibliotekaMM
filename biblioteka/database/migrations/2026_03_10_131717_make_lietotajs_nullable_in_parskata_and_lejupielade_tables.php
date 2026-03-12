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
       Schema::table('Parskata', function (Blueprint $table) {
        $table->integer('Lietotajs')->nullable()->change();
        });
        Schema::table('Lejupielade', function (Blueprint $table) {
            $table->integer('Lietotaja_ID')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Parskata', function (Blueprint $table) {
        $table->integer('Lietotajs')->nullable(false)->change();
        });
        Schema::table('Lejupielade', function (Blueprint $table) {
            $table->integer('Lietotaja_ID')->nullable(false)->change();
        });
    }
};
