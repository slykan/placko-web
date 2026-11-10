<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vozilo koje zaposlenik obično vozi: predlaže se na putnom nalogu i u loko vožnji.
        Schema::table('zaposlenici', function (Blueprint $table) {
            $table->string('registracija', 20)->nullable()->after('email');
            $table->string('vozilo', 100)->nullable()->after('registracija');
        });

        Schema::create('putni_nalozi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('zaposlenik_id')->nullable()->constrained('zaposlenici')->nullOnDelete();
            $table->foreignId('radni_nalog_id')->nullable()->constrained('radni_nalozi')->nullOnDelete();
            $table->string('broj');
            $table->unsignedInteger('redni_broj');
            $table->unsignedSmallInteger('godina');
            $table->string('status', 20)->default('otvoren');
            $table->string('vrsta', 10)->default('tuzemni');

            // putnik: zaposlenik ili npr. direktor / vlasnik koji nije u popisu zaposlenika
            $table->string('putnik');
            $table->string('radno_mjesto')->nullable();
            $table->date('datum_naloga');
            $table->string('nalogodavac')->nullable();

            $table->string('odrediste');
            $table->string('drzava', 80)->nullable();
            $table->text('svrha');

            $table->string('prijevoz', 20)->default('privatni_auto');
            $table->string('registracija', 20)->nullable();
            $table->string('vozilo', 100)->nullable();

            $table->dateTime('polazak_at')->nullable();
            $table->dateTime('povratak_at')->nullable();
            $table->dateTime('granica_odlazak_at')->nullable();
            $table->dateTime('granica_povratak_at')->nullable();

            $table->unsignedInteger('km_pocetno')->nullable();
            $table->unsignedInteger('km_zavrsno')->nullable();
            $table->decimal('km', 10, 1)->nullable();
            $table->unsignedTinyInteger('obroci')->default(0);
            $table->decimal('predujam', 10, 2)->default(0);
            $table->decimal('tecaj', 12, 6)->nullable();
            $table->text('izvjesce')->nullable();

            $table->decimal('ukupno_dnevnice', 10, 2)->default(0);
            $table->decimal('ukupno_km', 10, 2)->default(0);
            $table->decimal('ukupno_troskovi', 10, 2)->default(0);
            $table->decimal('za_isplatu', 10, 2)->default(0);
            $table->json('obracun')->nullable();
            $table->timestamps();

            $table->unique(['tvrtka_id', 'godina', 'redni_broj']);
        });

        Schema::create('putni_nalog_troskovi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('putni_nalog_id')->constrained('putni_nalozi')->cascadeOnDelete();
            $table->string('vrsta', 20);
            $table->string('opis')->nullable();
            $table->decimal('iznos', 10, 2);
            $table->string('privitak')->nullable();
            $table->unsignedInteger('redni_broj')->default(0);
            $table->timestamps();
        });

        Schema::create('loko_voznje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tvrtka_id')->constrained('tvrtke')->cascadeOnDelete();
            $table->foreignId('zaposlenik_id')->nullable()->constrained('zaposlenici')->nullOnDelete();
            $table->foreignId('radni_nalog_id')->nullable()->constrained('radni_nalozi')->nullOnDelete();
            $table->string('putnik');
            $table->date('datum');
            $table->time('vrijeme')->nullable();
            $table->string('relacija');
            $table->string('svrha');
            $table->decimal('km', 8, 1)->default(0);
            $table->string('registracija', 20)->nullable();
            $table->timestamps();

            $table->index(['tvrtka_id', 'datum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loko_voznje');
        Schema::dropIfExists('putni_nalog_troskovi');
        Schema::dropIfExists('putni_nalozi');
        Schema::table('zaposlenici', function (Blueprint $table) {
            $table->dropColumn(['registracija', 'vozilo']);
        });
    }
};
