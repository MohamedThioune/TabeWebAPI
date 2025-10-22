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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->enum('level', ['Important', 'Urgent', 'Info'])->nullable()->after('title');
            $table->text('body')->nullable()->after('level');
            $table->enum('model', ['transaction', 'card', 'profile', 'maintenance'])->nullable()->after('body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('body');
            $table->dropColumn('model');
            $table->dropColumn('level');
        });
    }
};
