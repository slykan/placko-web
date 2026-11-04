<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zaposlenici', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->string('ime_prezime');
            $table->string('radno_mjesto')->nullable();
            $table->string('kontakt_broj')->nullable();
            $table->string('email')->nullable();
            $table->decimal('cijena_sata', 10, 2)->nullable();
            $table->boolean('aktivan')->default(true);
            $table->text('napomena')->nullable();
            $table->timestamps();
        });

        Schema::create('radni_nalozi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('klijent_id')->nullable()->constrained('klijenti')->restrictOnDelete();
            $table->foreignId('nositelj_id')->nullable()->constrained('zaposlenici')->nullOnDelete();
            $table->foreignId('ponuda_id')->nullable()->constrained('ponude')->nullOnDelete();
            $table->foreignId('racun_id')->nullable()->constrained('racuni')->nullOnDelete();
            $table->string('broj');
            $table->unsignedInteger('redni_broj');
            $table->unsignedSmallInteger('godina');
            $table->string('status', 20)->default('otvoren');
            $table->date('datum_otvaranja');
            $table->date('rok')->nullable();
            $table->string('predmet')->nullable();
            $table->string('lokacija')->nullable();
            $table->text('opis_posla');
            $table->text('izvrseni_radovi')->nullable();
            $table->text('napomena')->nullable();
            $table->timestamp('zavrsen_at')->nullable();
            $table->decimal('ukupno_sati', 8, 2)->default(0);
            $table->decimal('ukupno_rad', 10, 2)->default(0);
            $table->decimal('ukupno_materijal', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['tvrtka_id', 'broj']);
            $table->unique(['tvrtka_id', 'godina', 'redni_broj']);
            $table->index(['tvrtka_id', 'status']);
        });

        Schema::create('radni_nalog_zaposlenik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radni_nalog_id')->constrained('radni_nalozi')->cascadeOnDelete();
            $table->foreignId('zaposlenik_id')->constrained('zaposlenici')->cascadeOnDelete();
            $table->unique(['radni_nalog_id', 'zaposlenik_id']);
        });

        Schema::create('radni_nalog_radovi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radni_nalog_id')->constrained('radni_nalozi')->cascadeOnDelete();
            $table->foreignId('zaposlenik_id')->nullable()->constrained('zaposlenici')->nullOnDelete();
            $table->date('datum');
            $table->decimal('sati', 6, 2);
            $table->decimal('cijena_sata', 10, 2)->nullable();
            $table->string('opis')->nullable();
            $table->unsignedSmallInteger('redni_broj')->default(1);
            $table->timestamps();

            $table->index(['zaposlenik_id', 'datum']);
        });

        Schema::create('radni_nalog_stavke', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radni_nalog_id')->constrained('radni_nalozi')->cascadeOnDelete();
            $table->foreignId('usluga_id')->nullable()->constrained('usluge')->nullOnDelete();
            $table->string('naziv');
            $table->string('opis')->nullable();
            $table->string('jedinica_mjere')->nullable()->default('kom');
            $table->decimal('kolicina', 10, 3)->default(1);
            $table->decimal('cijena', 10, 2)->default(0);
            $table->decimal('rabat_posto', 5, 2)->default(0);
            $table->decimal('pdv_stopa', 5, 2)->nullable();
            $table->decimal('ukupno', 10, 2)->default(0);
            $table->unsignedSmallInteger('redni_broj')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('radni_nalog_stavke');
        Schema::dropIfExists('radni_nalog_radovi');
        Schema::dropIfExists('radni_nalog_zaposlenik');
        Schema::dropIfExists('radni_nalozi');
        Schema::dropIfExists('zaposlenici');
    }
};
