<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Belgian municipalities (city pages are built per commune, not per postal locality),
 * the commune of each postal locality and provider, and the precomputed city pages
 * (category x commune) with their indexability.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 16)->unique();
            $table->json('names');
            $table->json('slugs');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->unsignedInteger('population')->nullable();
            $table->timestamps();
        });

        Schema::table('postal_codes', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();
            $table->index(['status', 'is_mobile', 'lat']);
        });

        Schema::create('city_pages', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('commune_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('providers_count');
            $table->boolean('is_indexable')->default(false);

            $table->primary(['category_id', 'commune_id']);
            $table->index(['category_id', 'is_indexable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_pages');

        Schema::table('providers', function (Blueprint $table) {
            $table->dropIndex(['status', 'is_mobile', 'lat']);
            $table->dropConstrainedForeignId('commune_id');
        });

        Schema::table('postal_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('commune_id');
        });

        Schema::dropIfExists('communes');
    }
};
