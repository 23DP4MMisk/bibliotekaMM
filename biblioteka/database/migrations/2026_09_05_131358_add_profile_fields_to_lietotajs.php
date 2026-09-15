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
            $table->string('foto', 255)->nullable()->after('epasts');
            $table->text('bio')->nullable()->after('foto');
            $table->string('pilseta', 100)->nullable()->after('bio');
            $table->date('dzim_datums')->nullable()->after('pilseta');
            $table->string('timekla_tema', 20)->default('light')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lietotajs', function (Blueprint $table) {
            //
            $table->dropColumn(['foto', 'bio', 'pilseta', 'dzim_datums', 'timekla_tema']);
        });
    }
};
