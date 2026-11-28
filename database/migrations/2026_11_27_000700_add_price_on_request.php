<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['products', 'services'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->boolean('price_on_request')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (['products', 'services'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('price_on_request');
            });
        }
    }
};
