<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dobavljaci', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->string('naziv');
            $table->string('oib')->nullable();
            $table->string('adresa')->nullable();
            $table->string('mjesto')->nullable();
            $table->string('kontakt_osoba')->nullable();
            $table->string('email')->nullable();
            $table->string('kontakt_broj')->nullable();
            $table->text('napomena')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dobavljaci');
    }
};
