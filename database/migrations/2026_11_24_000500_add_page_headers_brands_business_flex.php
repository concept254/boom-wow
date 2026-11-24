<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Additive only. Safe to run with `php artisan migrate` on an existing database. */
return new class extends Migration
{
    public function up(): void
    {
        // The old business "type" list drove behaviour. Behaviour now lives in two explicit fields so a new
        // business never needs a code change: `type` is just a free text label, `enquiry_type` picks the enquiry form.
        Schema::table('business_units', function (Blueprint $table) {
            $table->string('enquiry_type', 20)->default('general')->after('type');
        });
        DB::table('business_units')->where('type', 'it')->update(['enquiry_type' => 'project']);
        DB::table('business_units')->where('type', 'property')->update(['enquiry_type' => 'viewing']);
        DB::table('business_units')->where('type', 'events')->update(['enquiry_type' => 'event']);
        foreach (['salon' => 'Salon', 'liquor' => 'Liquor store', 'it' => 'IT consultants', 'property' => 'Property management', 'events' => 'Events management'] as $old => $label) {
            DB::table('business_units')->where('type', $old)->update(['type' => $label]);
        }

        Schema::create('page_headers', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();          // businesses, services, products ... or "default"
            $table->string('title')->nullable();           // optional override of the page title
            $table->string('subtitle', 300)->nullable();
            $table->string('image_path')->nullable();      // uploaded background image
            $table->string('image_url', 500)->nullable();  // or an external image URL
            $table->boolean('use_fallback')->default(true); // true: use the default/online image when none is set
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->boolean('show_on_home')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('published');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
        Schema::dropIfExists('page_headers');
        Schema::table('business_units', fn (Blueprint $t) => $t->dropColumn('enquiry_type'));
    }
};
