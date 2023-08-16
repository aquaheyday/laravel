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
            $table->integer('receptions_id');
            $table->string('email');
            $table->string('menu');
            $table->string('menu_type');
            $table->string('menu_size');
            $table->string('sub_menu')->nullable();
            $table->string('sub_menu_type')->nullable();
            $table->string('sub_menu_size')->nullable();
            $table->string('detail')->nullable();
            $table->string('pickup')->default('N');
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
