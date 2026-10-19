<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::latest()->get();

        return Inertia::render('Books/Index', [
            'books' => $books,
        ]);
    }

    public function create()
    {
        return Inertia::render('Books/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:50|unique:books,isbn',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        $validated['available_quantity'] = $validated['quantity'];

        Book::create($validated);

        return redirect('/books')
           
            ->with('success', 'Book created successfully.');
    }

    public function edit(Book $book)
    {
        return Inertia::render('Books/Edit', [
            'book' => $book,
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:50|unique:books,isbn,' . $book->id,
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        // Keep available copies consistent when total quantity changes.
        $borrowed = $book->quantity - $book->available_quantity;
        if ($validated['quantity'] < $borrowed) {
        return back()->withErrors([
            'quantity' => 'Quantity cannot be less than the number of borrowed books.',
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
