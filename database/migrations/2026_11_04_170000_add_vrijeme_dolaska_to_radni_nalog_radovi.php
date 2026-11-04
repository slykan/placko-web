<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radni_nalog_radovi', function (Blueprint $table) {
            $table->time('vrijeme_dolaska')->nullable()->after('datum');
        });
    }

    public function down(): void
    {
        Schema::table('radni_nalog_radovi', function (Blueprint $table) {
            $table->dropColumn('vrijeme_dolaska');
        });
    }
};
