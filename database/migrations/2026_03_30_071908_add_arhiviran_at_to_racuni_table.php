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
        Schema::table('racuni', function (Blueprint $table) {
            $table->timestamp('arhiviran_at')->nullable()->after('placen_at');
        });
    }

    public function down(): void
    {
        Schema::table('racuni', function (Blueprint $table) {
            $table->dropColumn('arhiviran_at');
        });
    }
};
