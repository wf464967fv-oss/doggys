<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Professional category slugs a guide links to ("find a pro near you").
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('related_categories')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('related_categories');
        });
    }
};
