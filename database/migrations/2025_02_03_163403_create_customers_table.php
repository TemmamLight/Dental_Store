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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string("phone_number")->nullable();
            $table->string('email')->unique()->nullable();
            $table->string("password");
            $table->string("verification_code")->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('city')->nullable();
            $table->string("address")->nullable();
            $table->string('photo')->nullable(); // image
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};