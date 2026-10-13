<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klijenti', function (Blueprint $table) {
            // Kupac je građanin (krajnja potrošnja) — od 1.1.2026. (Fiskalizacija 2.0) fiskaliziraju se
            // i računi takvim kupcima plaćeni na transakcijski račun.
            $table->boolean('fizicka_osoba')->default(false)->after('naziv');
        });
    }

    public function down(): void
    {
        Schema::table('klijenti', function (Blueprint $table) {
            $table->dropColumn('fizicka_osoba');
        });
    }
};
