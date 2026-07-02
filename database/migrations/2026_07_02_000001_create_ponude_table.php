<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ponude', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('klijent_id')->constrained('klijenti')->restrictOnDelete();
            $table->string('broj');
            $table->unsignedInteger('redni_broj');
            $table->unsignedSmallInteger('godina');
            $table->date('datum_izdavanja');
            $table->time('vrijeme_izdavanja')->nullable();
            $table->string('mjesto_izdavanja')->nullable();
            $table->unsignedSmallInteger('valjanost_dana')->default(30);
            $table->string('rok_ispostave')->nullable();
            $table->text('napomena')->nullable();
            $table->decimal('ukupno_osnovica', 10, 2)->default(0);
            $table->decimal('ukupno_rabat', 10, 2)->default(0);
            $table->decimal('ukupno_pdv', 10, 2)->default(0);
            $table->decimal('ukupno', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['tvrtka_id', 'broj']);
            $table->unique(['tvrtka_id', 'godina', 'redni_broj']);
        });

        Schema::create('ponuda_stavke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ponuda_id')->constrained('ponude')->cascadeOnDelete();
            $table->foreignId('usluga_id')->nullable()->constrained('usluge')->nullOnDelete();
            $table->string('naziv');
            $table->string('opis')->nullable();
            $table->string('jedinica_mjere')->nullable()->default('kom');
            $table->decimal('kolicina', 10, 3)->default(1);
            $table->decimal('cijena', 10, 2)->default(0);
            $table->decimal('rabat_posto', 5, 2)->default(0);
            $table->decimal('pdv_stopa', 5, 2)->nullable();
            $table->decimal('ukupno', 10, 2)->default(0);
            $table->unsignedTinyInteger('redni_broj')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ponuda_stavke');
        Schema::dropIfExists('ponude');
    }
};
