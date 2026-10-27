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
     * Display a listing of borrowings.
     */
    public function index()
    {
        $user = auth()->user();
        $isStaff = $user->can('issue books');

        $query = Borrowing::with(['user:id,name,email', 'book:id,title,isbn'])
            ->latest();

        // If member (non-staff), only show their own orders / borrowings
        if (!$isStaff) {
            $query->where('user_id', $user->id);
        }

        $borrowings = $query->get();

        return Inertia::render('Borrowings/Index', [
            'borrowings' => $borrowings,
            'isStaff' => $isStaff,
        ]);
    }

    /**
     * Member orders/borrows a book directly.
     */
    public function order(Book $book)
    {
        if ($book->available_quantity < 1) {
            return back()->with('error', 'Sorry, this book is currently out of stock.');
        }

        $userId = auth()->id();

        // Prevent duplicate active borrowing of the same book
        $alreadyBorrowed = Borrowing::where('user_id', $userId)
            ->where('book_id', $book->id)
            ->where('status', 'borrowed')
            ->exists();

        if ($alreadyBorrowed) {
            return back()->with('error', "You already have an active order for '{$book->title}'.");
        }

        // Decrement available quantity
        $book->decrement('available_quantity');

        // Create borrowing record with 14-day borrowing duration
        Borrowing::create([
            'user_id' => $userId,
            'book_id' => $book->id,
            'borrowed_at' => Carbon::today()->toDateString(),
            'due_date' => Carbon::today()->addDays(14)->toDateString(),
            'status' => 'borrowed',
        ]);

        return redirect('/borrowings')->with('success', "Order placed successfully! You borrowed '{$book->title}'. Return deadline is " . Carbon::today()->addDays(14)->format('M d, Y') . '.');
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
     * Display the specified borrowing record.
     */
    public function show(Borrowing $borrowing)
    {
        $user = auth()->user();
        $isStaff = $user->can('issue books');

        // If member (non-staff), only allow viewing their own borrowing
        if (!$isStaff && $borrowing->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $borrowing->load([
            'user:id,name,email',
            'book:id,title,author,isbn,price,quantity,available_quantity,category_id',
            'book.category:id,name',
        ]);

        return Inertia::render('Borrowings/Show', [
            'borrowing' => $borrowing,
            'isStaff' => $isStaff,
        ]);
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

        return back()->with('success', 'Book returned successfully.');
    }
}
