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
        Schema::create('payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('gross_amount');
            $table->integer('net_amount')->nullable();
            $table->integer('fees')->nullable();
            $table->string('currency')->default('FCFA');
            $table->enum('status', ['authorized', 'completed', 'cancelled', 'failed'])->default('authorized');
            $table->string('reference_number')->nullable()->unique();
            $table->string('transaction_id')->nullable()->unique();
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::drop('payouts');
    }
};
