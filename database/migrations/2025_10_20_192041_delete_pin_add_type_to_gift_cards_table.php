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
            $table->dropColumn('pin_hash');
            $table->dropColumn('pin_mask');
            $table->enum('type', ['physical', 'digital'])->default('digital');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->text('pin_hash');
            $table->char('pin_mask', 6);
            $table->dropColumn('type');
        });
    }
};
