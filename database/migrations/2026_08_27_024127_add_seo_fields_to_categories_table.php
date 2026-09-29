<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('main_categories', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // For categories, slug uniqueness is combination of main_categories_id and slug
            $table->unique(['main_categories_id', 'slug']);
        });

        // Automatically generate slugs for existing data
        $mainCategories = DB::table('main_categories')->get();
        foreach ($mainCategories as $mc) {
            DB::table('main_categories')
                ->where('id', $mc->id)
                ->update(['slug' => Str::slug($mc->title)]); 
        }

        $categories = DB::table('categories')->get();
        foreach ($categories as $cat) {
            DB::table('categories')
                ->where('id', $cat->id)
                ->update(['slug' => Str::slug($cat->title)]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['main_categories_id', 'slug']);
            $table->dropColumn(['slug', 'meta_title', 'meta_description']);
        });

        Schema::table('main_categories', function (Blueprint $table) {
            $table->dropColumn(['slug', 'meta_title', 'meta_description']);
        });
    }
};
