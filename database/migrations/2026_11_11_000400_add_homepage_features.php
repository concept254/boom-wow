<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Additive only: new tables + new columns. Safe to run with `php artisan migrate` on an existing database. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30)->default('general'); // general, store, service, product, announcement, promotion
            $table->string('caption')->nullable();           // small label above the heading
            $table->string('heading');
            $table->string('subheading', 500)->nullable();
            $table->string('image_path')->nullable();        // uploaded image (public disk)
            $table->string('image_url', 500)->nullable();    // or an external image URL
            $table->string('cta_label', 60)->nullable();
            $table->string('cta_url')->nullable();
            $table->string('cta2_label', 60)->nullable();
            $table->string('cta2_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->string('status', 20)->default('published');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order']);
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('job_title')->nullable();
            $table->string('team_group', 30)->default('management'); // management, team
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('email')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('published');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->string('placement', 30)->default('home_specials'); // top_bar, home_specials, home_banner, inner_banner
            $table->string('badge', 40)->nullable();                   // e.g. "SPECIAL", "-20%"
            $table->string('cta_label', 60)->nullable();
            $table->string('theme', 20)->default('orange');            // orange, charcoal, light
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::table('business_units', function (Blueprint $table) {
            $table->boolean('is_featured')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('business_units', fn (Blueprint $t) => $t->dropColumn('is_featured'));
        Schema::table('promotions', fn (Blueprint $t) => $t->dropColumn(['placement', 'badge', 'cta_label', 'theme', 'sort_order']));
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('hero_slides');
    }
};
