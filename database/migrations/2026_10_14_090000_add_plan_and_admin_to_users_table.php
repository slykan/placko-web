<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan', 20)->default('starter')->after('password');
            // Do kada vrijedi plaćeni paket; nakon toga korisnik je ponovno Starter. Prazno = bez isteka.
            $table->date('plan_do')->nullable()->after('plan');
            $table->boolean('is_admin')->default(false)->after('plan_do');
        });

        DB::table('users')->where('email', 'slynetwork@gmail.com')->update(['is_admin' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['plan', 'plan_do', 'is_admin']);
        });
    }
};
