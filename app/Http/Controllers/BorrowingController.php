<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BorrowingController extends Controller
{
    /**
     * Display a listing of all borrowings.
     */
    public function index()
    {
        $borrowings = Borrowing::with(['user:id,name,email', 'book:id,title,isbn'])
            ->latest()
            ->get();

        return Inertia::render('Borrowings/Index', [
            'borrowings' => $borrowings,
        ]);
    }

    /**
     * Show the form for issuing a book.
     */
    public function create()
    {
        // Only fetch books that currently have available copies
        $books = Book::where('available_quantity', '>', 0)
            ->select('id', 'title', 'available_quantity')
            ->get();

        $users = User::select('id', 'name', 'email')->get();

        return Inertia::render('Borrowings/Create', [
            'books' => $books,
            'users' => $users,
        ]);
    }

    /**
     * Issue a book to a user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'borrowed_at' => 'required|date',
            'due_date' => 'required|date|after_or_equal:borrowed_at',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if ($book->available_quantity < 1) {
            return back()->withErrors([
                'book_id' => 'This book is currently out of stock.',
            ]);
        }

        // Decrement available copies
        $book->decrement('available_quantity');

        Borrowing::create([
            'user_id' => $validated['user_id'],
            'book_id' => $validated['book_id'],
            'borrowed_at' => $validated['borrowed_at'],
            'due_date' => $validated['due_date'],
            'status' => 'borrowed',
        ]);

        return redirect('/borrowings')->with('success', 'Book issued successfully.');
    }

    /**
     * Mark a book as returned.
     */
    public function returnBook(Borrowing $borrowing)
    {
        if ($borrowing->status === 'returned') {
            return back()->withErrors(['message' => 'Book already marked as returned.']);
        }

        $today = Carbon::today();
        $dueDate = Carbon::parse($borrowing->due_date);
        $fine = 0;

        // If returned late, charge 5 per day late
        if ($today->gt($dueDate)) {
            $daysLate = $today->diffInDays($dueDate);
            $fine = $daysLate * 5.00;
        }

        // Increment available copies back
        $borrowing->book->increment('available_quantity');

        $borrowing->update([
            'returned_at' => $today->toDateString(),
            'status' => 'returned',
            'fine' => $fine,
        ]);

        return redirect('/borrowings')->with('success', 'Book returned successfully.');
    }
}
