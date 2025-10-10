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
            $table->foreignUuid('owner_user_id')->constrained('users');
            $table->foreignUuid('beneficiary_id')->constrained('beneficiaries');
            $table->foreignid('design_id')->constrained('designs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropForeign('gift_cards_owner_user_id');
            $table->dropColumn('owner_user_id');
            $table->dropForeign('gift_cards_beneficiary_id');
            $table->dropColumn('beneficiary_id');
            $table->dropForeign('gift_cards_design_id');
            $table->dropColumn('design_id');
        });
    }
};
