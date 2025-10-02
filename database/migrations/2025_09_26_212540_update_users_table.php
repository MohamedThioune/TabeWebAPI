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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('kyc_status');
            $table->string('whatsApp')->after('email')->unique();
            $table->string('email')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('kyc_status', ['pending', 'verified', 'rejected', 'not_submitted'])->default('not_submitted');
            $table->dropColumn('whatsApp');
            $table->string('email')->nullable()->change();
        });
    }
};
