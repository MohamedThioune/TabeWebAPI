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
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->uuid('enterprise_id')->nullable()->after('email')->default(NULL);
        });
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropForeign('beneficiaries_enterprise_id_foreign');
        });
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropColumn('enterprise_id');
        });
    }
};
