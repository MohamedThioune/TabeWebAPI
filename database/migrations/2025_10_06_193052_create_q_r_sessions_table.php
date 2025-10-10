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
        Schema::create('qr_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('token');
            $table->text('url');
            $table->timestamp('expired_at');
            $table->foreignUuid('gift_card_id')->constrained('gift_cards');
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
        Schema::drop('qr_sessions');
    }
};
