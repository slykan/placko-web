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
            $table->string('zki')->nullable()->after('ukupno');
            $table->string('jir')->nullable()->after('zki');
            $table->timestamp('fiskaliziran_at')->nullable()->after('jir');
        });
    }

    public function down(): void
    {
        Schema::table('racuni', function (Blueprint $table) {
            $table->dropColumn(['zki', 'jir', 'fiskaliziran_at']);
        });
    }
};
