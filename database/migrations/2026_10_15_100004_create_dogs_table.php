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
        Schema::create('dogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('breed', 120)->nullable();
            $table->string('sex', 8)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('microchip_number', 32)->nullable();
            $table->boolean('is_neutered')->default(false);
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->json('vaccinations')->nullable();
            $table->text('health_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dogs');
    }
};
