<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('racuni', function (Blueprint $table) {
            $table->dropUnique('racuni_broj_unique');
            $table->unique(['tvrtka_id', 'broj'], 'racuni_tvrtka_broj_unique');
        });
    }

    public function down(): void
    {
        Schema::table('racuni', function (Blueprint $table) {
            $table->dropUnique('racuni_tvrtka_broj_unique');
            $table->unique('broj', 'racuni_broj_unique');
        });
    }
};
