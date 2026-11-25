<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Additive only: service galleries, product/service reviews, and "key features" lists. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->morphs('reviewable');                 // Product or Service
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->unsignedTinyInteger('rating');        // 1-5
            $table->string('title', 150)->nullable();
            $table->text('body');
            $table->boolean('is_verified')->default(false); // reviewer really bought / booked it
            $table->string('status', 20)->default('pending'); // pending | published | rejected
            $table->text('reply')->nullable();            // public reply from the business
            $table->timestamps();
            $table->softDeletes();
            $table->index(['reviewable_type', 'reviewable_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('short_description', 300)->nullable()->after('description');
            $table->json('highlights')->nullable()->after('short_description');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->json('highlights')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('services', fn (Blueprint $t) => $t->dropColumn('highlights'));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['short_description', 'highlights']));
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('service_images');
    }
};
