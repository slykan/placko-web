<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Početni broj računa za godinu u kojoj tvrtka prelazi na Plačko (već je izdala
        // račune u drugom programu). Vrijedi samo za tu godinu — od nove kreće od 1.
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->unsignedInteger('racun_pocetni_broj')->nullable();
            $table->unsignedSmallInteger('racun_pocetni_godina')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->dropColumn(['racun_pocetni_broj', 'racun_pocetni_godina']);
        });
    }
};
