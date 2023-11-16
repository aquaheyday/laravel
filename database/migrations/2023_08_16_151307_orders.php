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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('room_id');
            $table->integer('user_id');
            $table->string('menu');
            $table->string('menu_type');
            $table->string('menu_size');
            $table->string('menu_detail')->nullable();
            $table->string('sub_menu')->nullable();
            $table->string('sub_menu_type')->nullable();
            $table->string('sub_menu_size')->nullable();
            $table->string('sub_menu_detail')->nullable();
            $table->string('pick_up_yn', 1)->default('N');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
