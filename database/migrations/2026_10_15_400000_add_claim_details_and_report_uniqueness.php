<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Claims: the claimant's position in the company, the language of their e-mails and whether the claim
     * comes with a brand-new listing ("add your business", hidden until approved).
     * Review reports: one report per review and per user.
     */
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('position', 100)->nullable()->after('bce_number');
            $table->string('locale', 2)->default('fr')->after('action');
            $table->boolean('creates_listing')->default(false)->after('locale');
            $table->index(['provider_id', 'user_id', 'status']);
        });

        Schema::table('review_reports', function (Blueprint $table) {
            $table->unique(['review_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_reports', function (Blueprint $table) {
            $table->dropUnique(['review_id', 'user_id']);
        });

        Schema::table('claims', function (Blueprint $table) {
            $table->dropIndex(['provider_id', 'user_id', 'status']);
            $table->dropColumn(['position', 'locale', 'creates_listing']);
        });
    }
};
