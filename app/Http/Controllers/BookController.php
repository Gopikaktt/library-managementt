<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('category')->latest();

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        $books = $query->get();
        $categories = Category::withCount('books')->orderBy('name')->get();

        return Inertia::render('Books/Index', [
            'books' => $books,
            'categories' => $categories,
            'selectedCategory' => $request->category_id ? (int)$request->category_id : null,
            'searchTerm' => $request->search ?? '',
        ]);
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return Inertia::render('Books/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:50|unique:books,isbn',
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ], [
            'category_id.required' => 'Please select a book category.',
            'category_id.exists' => 'The selected category does not exist.',
        ]);

        $category = Category::find($validated['category_id']);
        $validated['category'] = $category ? $category->name : '';
        $validated['available_quantity'] = $validated['quantity'];

        Book::create($validated);

        return redirect('/books')
            ->with('success', 'Book created successfully.');
    }

    public function show(Book $book)
    {
        $book->load([
            'category',
            'borrowings' => function ($q) {
                $q->with('user:id,name,email')->latest()->take(10);
            }
        ]);

        return Inertia::render('Books/Show', [
            'book' => $book,
        ]);
    }

    public function edit(Book $book)
    {
        $categories = Category::orderBy('name')->get();

        return Inertia::render('Books/Edit', [
            'book' => $book->load('category'),
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:50|unique:books,isbn,' . $book->id,
            'category_id' => 'required|exists:categories,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ], [
            'category_id.required' => 'Please select a book category.',
            'category_id.exists' => 'The selected category does not exist.',
        ]);

        $category = Category::find($validated['category_id']);
        $validated['category'] = $category ? $category->name : '';

        // Keep available copies consistent when total quantity changes.
        $borrowed = $book->quantity - $book->available_quantity;
        if ($validated['quantity'] < $borrowed) {
            return back()->withErrors([
                'quantity' => 'Quantity cannot be less than the number of borrowed books (' . $borrowed . ').',
            ]);
        }

        $validated['available_quantity'] = max(
            0,
            $validated['quantity'] - $borrowed
        );

        $book->update($validated);

        return redirect('/books')
            ->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect('/books')
            ->with('success', 'Book deleted successfully.');
    }
}
