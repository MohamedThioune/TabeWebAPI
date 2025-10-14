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
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropForeign('gift_cards_beneficiary_id_foreign');
            $table->dropForeign('gift_cards_design_id_foreign');
        });

        Schema::table('gift_cards', function (Blueprint $table) {
            $table->string('beneficiary_id')->nullable()->change();
            $table->unsignedBigInteger('design_id')->default(1)->change();
        });

        Schema::table('gift_cards', function (Blueprint $table) {
            $table->foreign('beneficiary_id')->references('id')->on('beneficiaries');
            $table->foreign('design_id')->references('id')->on('designs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropForeign('gift_cards_beneficiary_id_foreign');
            $table->dropForeign('gift_cards_design_id_foreign');
        });

        Schema::table('gift_cards', function (Blueprint $table) {
            $table->string('beneficiary_id')->change();
            $table->integer('design_id')->change();
        });

        Schema::table('gift_cards', function (Blueprint $table) {
            $table->foreign('beneficiary_id')->references('id')->on('beneficiaries');
            $table->foreign('design_id')->references('id')->on('designs');
        });
    }
};
