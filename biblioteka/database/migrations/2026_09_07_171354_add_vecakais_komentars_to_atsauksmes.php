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
            // Pievieno vecākā komentāra ID (nullable, jo ne visiem komentāriem ir vecākais)
            $table->integer('vecakais_komentars')->nullable()->after('komentārs');
            
            // Ārējā atslēga, kas norāda uz to pašu tabulu
            $table->foreign('vecakais_komentars')
                  ->references('Atsauksmes_ID')
                  ->on('Atsauksmes')
                  ->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('atsauksmes', function (Blueprint $table) {
            $table->dropForeign(['vecakais_komentars']);
            $table->dropColumn('vecakais_komentars');
        });
    }
};
