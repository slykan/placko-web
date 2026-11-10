<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // zadano uključeno; preglednik ili Android svejedno traže dopuštenje na uređaju
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('push_obavijesti')->default(true);
        });

        // web push: jedan red po pregledniku
        Schema::create('push_pretplate', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint');
            $table->char('endpoint_hash', 64)->unique();
            $table->string('p256dh', 255);
            $table->string('auth', 64);
            $table->timestamps();
        });

        // Android aplikacija (Firebase Cloud Messaging)
        Schema::create('mobile_push_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->text('token');
            $table->char('session_hash', 64)->index();
            $table->timestamps();
        });

        // koje su novosti već poslane kao push, da se ista objava ne šalje dvaput
        Schema::create('push_novosti_poslano', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 191)->unique();
            $table->unsignedInteger('uredjaja')->default(0);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_novosti_poslano');
        Schema::dropIfExists('mobile_push_devices');
        Schema::dropIfExists('push_pretplate');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('push_obavijesti');
        });
    }
};
