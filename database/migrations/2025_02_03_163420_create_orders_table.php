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
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
            $table->string('number')->unique();
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'processing', 'shipping', 'completed', 'declined'])->default('pending');
            $table->enum('order_type', ['regular', 'custom'])->default('regular');
            $table->decimal('shipping_price')->nullable();
            $table->longText('notes')->nullable();
            $table->softDeletes();
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