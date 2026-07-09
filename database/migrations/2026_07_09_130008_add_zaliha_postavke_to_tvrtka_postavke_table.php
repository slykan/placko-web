<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->boolean('zaliha_dozvoli_negativnu')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('tvrtka_postavke', function (Blueprint $table) {
            $table->dropColumn('zaliha_dozvoli_negativnu');
        });
    }
};
