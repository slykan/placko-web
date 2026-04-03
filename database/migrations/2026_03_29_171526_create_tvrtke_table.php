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
        Schema::create('tvrtke', function (Blueprint $table) {
            $table->id();
            $table->string('naziv');
            $table->string('nkd')->nullable();
            $table->string('adresa')->nullable();
            $table->string('mjesto')->nullable();
            $table->string('po_broj')->nullable();
            $table->string('vlasnik')->nullable();
            $table->string('oib', 11)->nullable();
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->string('banka')->nullable();
            $table->string('djelatnost')->nullable();
            $table->string('kontakt_broj')->nullable();
            $table->string('email')->nullable();
            $table->string('web_mjesto')->nullable();
            $table->string('oznaka_operatera', 10)->nullable();
            $table->string('logo')->nullable();
            $table->text('napomena')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tvrtke');
    }
};
