<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ponude', function (Blueprint $table) {
            $table->timestamp('prihvacena_at')->nullable()->after('rok_ispostave');
        });
    }

    public function down(): void
    {
        Schema::table('ponude', function (Blueprint $table) {
            $table->dropColumn('prihvacena_at');
        });
    }
};
