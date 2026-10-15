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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('street')->nullable();
            $table->string('house_number', 16)->nullable();
            $table->string('postal_code', 4)->nullable()->index();
            $table->string('city')->nullable();
            $table->string('region', 16)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('opening_hours')->nullable();
            $table->boolean('is_mobile')->default(false);
            $table->unsignedSmallInteger('service_radius_km')->nullable();
            $table->string('bce_number', 16)->nullable()->index();
            $table->string('regulatory_number', 64)->nullable();
            $table->string('verification_level', 16)->default('none');
            $table->string('status', 16)->default('active')->index();
            $table->string('plan', 16)->default('free');
            $table->foreignId('claimed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 16)->default('manual');
            $table->string('source_ref')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->decimal('rating_avg', 3, 2)->nullable();
            $table->unsignedInteger('reviews_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['lat', 'lng']);
            $table->unique(['source', 'source_ref']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
