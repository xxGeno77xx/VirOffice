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
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->string("compte_a_debiter")->nullable();
            $table->string("libelle")->nullable();
            $table->string("type_vo")->nullable();
            $table->integer("periodicite")->nullable();
            $table->integer("montant_total")->nullable();
            $table->integer("nombre_traite")->nullable();
            $table->integer("montant_vo")->nullable();
            $table->integer("montant_fin")->nullable();
            $table->date("date_debut")->nullable();
            $table->date("date_fin")->nullable();
            $table->string("compte_a_crediter")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
