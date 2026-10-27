<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Book;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('books')
            ->with(['books' => function ($q) {
                $q->select('id', 'category_id', 'title', 'author', 'isbn', 'available_quantity', 'quantity')
                  ->latest();
            }])
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function show(Category $category)
    {
        $category->loadCount('books')
            ->load(['books' => function ($q) {
                $q->latest();
            }]);

        return Inertia::render('Categories/Show', [
            'category' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        Category::create($validated);

        return redirect()->back()
            ->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($validated);

        // Keep book category string in sync
        Book::where('category_id', $category->id)->update([
            'category' => $category->name,
        ]);

        return redirect()->back()
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $bookCount = $category->books()->count();

        // Foreign key will set category_id to null for books, let's also clear category name
        Book::where('category_id', $category->id)->update([
            'category' => null,
            'category_id' => null,
        ]);

        $category->delete();

        $message = $bookCount > 0
            ? "Category deleted successfully. {$bookCount} book(s) were unassigned."
            : 'Category deleted successfully.';

        return redirect()->back()->with('success', $message);
    }
}
