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
        Schema::table('lietotajs', function (Blueprint $table) {
            //
            if (Schema::hasColumn('Lietotajs', 'timekla_tema')) {
                $table->dropColumn('timekla_tema');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lietotajs', function (Blueprint $table) {
            //
            $table->string('timekla_tema', 20)->default('light')->after('status');
        });
    }
};
