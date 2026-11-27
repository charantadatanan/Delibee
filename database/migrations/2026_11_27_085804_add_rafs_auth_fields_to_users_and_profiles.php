<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->nullable()->unique();
            $table->string('email_verification_code_hash')->nullable();
            $table->timestamp('email_verification_code_expires_at')->nullable();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->char('phone_country', 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('phone_country');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn([
                'username',
                'email_verification_code_hash',
                'email_verification_code_expires_at',
            ]);
        });
    }
};
