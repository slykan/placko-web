<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tvrtke', function (Blueprint $table) {
            $table->string('vrsta_poslovanja')->default('pausalni_obrt')->after('naziv');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtke', function (Blueprint $table) {
            $table->dropColumn('vrsta_poslovanja');
        });
    }
};
