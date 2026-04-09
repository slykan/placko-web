<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->boolean('eracun_demo')->default(false)->after('eracun_aktivan');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->dropColumn('eracun_demo');
        });
    }
};
