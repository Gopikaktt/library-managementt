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
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('books', 'category_id')) {
            Schema::table('books', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('isbn')
                    ->constrained('categories')
                    ->nullOnDelete();
                $table->string('category')->nullable()->change();
            });
        }

        // Seed initial categories from existing books and standard categories
        $existingCategories = \Illuminate\Support\Facades\DB::table('books')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->pluck('category')
            ->unique();

        $defaultCategories = collect([
            'Fiction',
            'Non-Fiction',
            'Science & Technology',
            'History',
            'Literature',
            'Computer Science',
            'Philosophy',
            'Business & Economics',
        ])->merge($existingCategories)->unique();

        $now = now();
        foreach ($defaultCategories as $catName) {
            $catName = trim($catName);
            if (!empty($catName)) {
                \Illuminate\Support\Facades\DB::table('categories')->updateOrInsert(
                    ['name' => $catName],
                    ['updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        // Link existing books to their category_id
        $categoryMap = \Illuminate\Support\Facades\DB::table('categories')->pluck('id', 'name');
        $books = \Illuminate\Support\Facades\DB::table('books')->get();
        foreach ($books as $b) {
            if (!empty($b->category) && isset($categoryMap[$b->category])) {
                \Illuminate\Support\Facades\DB::table('books')
                    ->where('id', $b->id)
                    ->update(['category_id' => $categoryMap[$b->category]]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('books', 'category_id')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        Schema::dropIfExists('categories');
    }
};
