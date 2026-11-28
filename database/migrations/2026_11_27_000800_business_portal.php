<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_units', function (Blueprint $table) {
            $table->string('about_title')->nullable();
            $table->longText('about_body')->nullable();
            $table->json('highlights')->nullable();
            // Section switches for the business's own page (all on by default).
            $table->boolean('show_about')->default(true);
            $table->boolean('show_gallery')->default(true);
            $table->boolean('show_articles')->default(true);
            $table->boolean('show_promos')->default(true);
            $table->boolean('show_team')->default(true);
        });

        Schema::create('business_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_images');
        Schema::table('business_units', function (Blueprint $table) {
            $table->dropColumn(['about_title', 'about_body', 'highlights', 'show_about', 'show_gallery', 'show_articles', 'show_promos', 'show_team']);
        });
    }
};
