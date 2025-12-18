<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', ['authorized', 'captured', 'cancelled', 'refunded', 'failed'])->default('authorized');
            $table->string('amount');
            $table->string('currency')->default('FCFA');
            $table->string('gift_card_id');
            $table->string('user_id');
            $table->foreign('gift_card_id')->references('id')->on('gift_cards');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transactions');
    }
};
