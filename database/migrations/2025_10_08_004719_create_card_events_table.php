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
        Schema::create('card_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->json('meta_json')->nullable();
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
        Schema::drop('card_events');
    }
};
