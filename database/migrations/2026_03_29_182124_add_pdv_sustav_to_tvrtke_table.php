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
            $table->boolean('u_sustavu_pdv')->default(false)->after('napomena');
        });
    }

    public function down(): void
    {
        Schema::table('tvrtke', function (Blueprint $table) {
            $table->dropColumn('u_sustavu_pdv');
        });
    }
};
