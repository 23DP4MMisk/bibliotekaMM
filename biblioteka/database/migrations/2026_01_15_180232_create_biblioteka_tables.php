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
       // Nodaļa 
        Schema::create('Nodala', function (Blueprint $table) {
            $table->integer('Nodala_ID')->autoIncrement(); // ID для связи с Zanrs
            $table->enum('tips', ['akademiska', 'izglitojosa'])->default('akademiska');
            $table->timestamps();
        });

        // Lietotajs 
        Schema::create('Lietotajs', function (Blueprint $table) {
            $table->integer('kodsID')->autoIncrement();
            $table->string('lietotaja_vards', 10);
            $table->string('epasts', 20)->unique();
            $table->string('parole', 255);
            $table->enum('loma', ['admins', 'registretajsklients', 'viesis'])->default('viesis');
            $table->date('registresanas_datums');
            $table->enum('status', ['aktivs', 'blokets'])->default('aktivs');
            $table->timestamps();
        });

        // Zanrs
        Schema::create('Zanrs', function (Blueprint $table) {
            $table->integer('Zanra_ID')->autoIncrement();
            $table->string('nosaukums', 40)->unique();
            $table->integer('gramatu_skaits')->default(0);
            $table->integer('Nodala')->nullable();
            $table->timestamps();
        });

         // Zanrs uz Nodaļa
        Schema::table('Zanrs', function (Blueprint $table) {
            $table->foreign('Nodala')->references('Nodala_ID')->on('Nodala');
        });

        // Gramata 
        Schema::create('Gramata', function (Blueprint $table) {
            $table->integer('ISBN')->primary();
            $table->string('nosaukums', 50);
            $table->string('gads', 4)->nullable();
            $table->string('apraksts', 1000)->nullable();
            $table->integer('lapu_skaits')->nullable();
            $table->string('faila_pdf', 250)->nullable();
            $table->string('vaku_attels', 250)->nullable();
            $table->string('autors', 20);
            $table->integer('Zanra_ID')->nullable();
            $table->timestamps();
        });

         //Arejas atslegas   Gramata UZ Zanrs
        Schema::table('Gramata', function (Blueprint $table) {
            $table->foreign('Zanra_ID')->references('Zanra_ID')->on('Zanrs');
        });



        // LietotajGramatas 
        Schema::create('LietotajGramatas', function (Blueprint $table) {
            $table->integer('LietotajGramatas_ID')->autoIncrement();
            $table->date('pievienosanas_datums');
            $table->enum('statuss', ['lasu', 'izlasiju', 'vel nelasiju'])->default('vel nelasiju');
            $table->integer('Lietotajs');
            $table->integer('Gramatas');
            $table->timestamps();
        });

        //Arejas atslegas  LietotajGramatas uz zanriem un gramatam 
        Schema::table('LietotajGramatas', function (Blueprint $table) {
            $table->foreign('Lietotajs')->references('kodsID')->on('Lietotajs');
            $table->foreign('Gramatas')->references('ISBN')->on('Gramata');
        });

        // Atsauksmes 
        Schema::create('Atsauksmes', function (Blueprint $table) {
            $table->integer('Atsauksmes_ID')->autoIncrement();
            $table->integer('Lietotaja_ID');
            $table->integer('Gramatas_ID');
            $table->integer('vertejums');
            $table->string('komentārs', 500)->nullable();
            $table->timestamps();
        });
        //  Arejas atslegas Atsauksmes uz lietotajam un gramatam 
        Schema::table('Atsauksmes', function (Blueprint $table) {
            $table->foreign('Lietotaja_ID')->references('kodsID')->on('Lietotajs');
            $table->foreign('Gramatas_ID')->references('ISBN')->on('Gramata');

        });

        

        // Parskata 
        Schema::create('Parskata', function (Blueprint $table) {
            $table->integer('Parskata_ID')->autoIncrement();
            $table->integer('parskatas_statuss');
            $table->integer('Gramatas');
            $table->integer('Lietotajs');
            $table->timestamps();
        });
         // Arejas atslegas Parskata
        Schema::table('Parskata', function (Blueprint $table) {
            $table->foreign('Gramatas')->references('ISBN')->on('Gramata');
            $table->foreign('Lietotajs')->references('kodsID')->on('Lietotajs');
        });

        // 8.  Lejupielade 
        Schema::create('Lejupielade', function (Blueprint $table) {
            $table->integer('Lejupielade_ID')->autoIncrement();
            $table->date('Datums');
            $table->integer('Gramatas_ID');
            $table->integer('Lietotaja_ID');
            $table->timestamps();
        });
         // Arejas atslegas Lejupielade
        Schema::table('Lejupielade', function (Blueprint $table) {
            $table->foreign('Gramatas_ID')->references('ISBN')->on('Gramata');
            $table->foreign('Lietotaja_ID')->references('kodsID')->on('Lietotajs');
        });






    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        

        // dzešam tabulas  
        Schema::dropIfExists('Lejupielade');
        Schema::dropIfExists('Parskata');
        Schema::dropIfExists('Atsauksmes');
        Schema::dropIfExists('LietotajGramatas');
        Schema::dropIfExists('Gramata');
        Schema::dropIfExists('Zanrs');
        Schema::dropIfExists('Lietotajs');
        Schema::dropIfExists('Nodala');
    }
};
