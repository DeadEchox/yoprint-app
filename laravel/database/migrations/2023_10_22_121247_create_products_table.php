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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('unique_key');
            $table->string('title');
            $table->text('description');
            $table->string('style#');
            $table->string('color_name');
            $table->string('size');
            $table->string('sanmar_mainframe_color');
            $table->decimal('piece_price', 8, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
