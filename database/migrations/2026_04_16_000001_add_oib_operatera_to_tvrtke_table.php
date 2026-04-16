<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tvrtke', function (Blueprint $table) {
            $table->string('oib_operatera', 11)->nullable()->after('oznaka_operatera');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtke', function (Blueprint $table) {
            $table->dropColumn('oib_operatera');
        });
    }
};
