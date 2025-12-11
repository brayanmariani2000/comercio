<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nombre')->default('Mi lista de deseos');
            $table->boolean('publica')->default(false);
            $table->boolean('predeterminada')->default(true);
            $table->timestamps();
        });
        
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wishlist_id')->constrained('wishlists')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos');
            $table->timestamps();
            
            $table->unique(['wishlist_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
        Schema::dropIfExists('wishlists');
    }
};