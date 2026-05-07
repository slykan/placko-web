<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tvrtke', function (Blueprint $table) {
            $table->string('pnb', 50)->nullable()->default('HR00')->after('iban');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtke', function (Blueprint $table) {
            $table->dropColumn('pnb');
        });
    }
};
