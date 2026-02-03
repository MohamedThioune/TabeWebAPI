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
        Schema::table('transactions', function (Blueprint $table) {
            $table->uuid('next_transaction_id')->nullable()->after('user_id');
            $table->foreign('next_transaction_id')->references('id')->on('transactions')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('parent_transaction_id')->nullable()->after('user_id');
            $table->foreign('parent_transaction_id')->references('id')->on('transactions')->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::table('payouts', function (Blueprint $table) {
            $table->uuid('next_payout_id')->nullable()->after('user_id');
            $table->foreign('next_payout_id')->references('id')->on('payouts')->onDelete('cascade')->onUpdate('cascade');
            $table->uuid('parent_payout_id')->nullable()->after('user_id');
            $table->foreign('parent_payout_id')->references('id')->on('payouts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('next_transaction_id');
            $table->dropForeign('transactions_next_transaction_id_foreign');
            $table->dropColumn('parent_transaction_id');
            $table->dropForeign('transactions_parent_transaction_id_foreign');
        });
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn('next_payout_id');
            $table->dropForeign('payouts_next_payout_id_foreign');
            $table->dropColumn('parent_payout_id');
            $table->dropForeign('payouts_parent_payout_id_foreign');
        });
    }
};
