<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the analytics and operations dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $isStaff = $user->can('issue books') || $user->hasAnyRole(['Admin', 'Librarian']);
        $today = Carbon::today();

        // 1. Core KPIs
        $stats = [
            'total_books' => Book::count(),
            'total_copies' => (int) Book::sum('quantity'),
            'active_borrowings' => Borrowing::where('status', 'borrowed')->count(),
            'overdue_borrowings' => Borrowing::where('status', 'borrowed')
                ->where('due_date', '<', $today)
                ->count(),
            'out_of_stock_count' => Book::where('available_quantity', '<=', 0)->count(),
            'low_stock_count' => Book::where('available_quantity', '>', 0)
                ->where('available_quantity', '<=', 2)
                ->count(),
            'total_members' => User::count(),
            'total_categories' => Category::count(),
            'returned_count' => Borrowing::where('status', 'returned')->count(),
        ];

        // Personal stats if member
        $memberStats = null;
        if (!$isStaff) {
            $memberStats = [
                'my_active_loans' => Borrowing::where('user_id', $user->id)->where('status', 'borrowed')->count(),
                'my_overdue_loans' => Borrowing::where('user_id', $user->id)
                    ->where('status', 'borrowed')
                    ->where('due_date', '<', $today)
                    ->count(),
                'my_total_borrowed' => Borrowing::where('user_id', $user->id)->count(),
            ];
        }

        // 2. Most Popular Books (highest borrowing count)
        $popularBooks = Book::withCount('borrowings')
            ->with('category:id,name')
            ->orderByDesc('borrowings_count')
            ->take(6)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'isbn' => $book->isbn,
                    'category_name' => $book->category?->name ?? 'General',
                    'borrowings_count' => $book->borrowings_count,
                    'available_quantity' => $book->available_quantity,
                    'quantity' => $book->quantity,
                    'is_out_of_stock' => $book->available_quantity <= 0,
                ];
            });

        // 3. Out of Stock Books
        $outOfStockBooks = Book::with('category:id,name')
            ->where('available_quantity', '<=', 0)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'isbn' => $book->isbn,
                    'category_name' => $book->category?->name ?? 'General',
                    'quantity' => $book->quantity,
                    'available_quantity' => $book->available_quantity,
                ];
            });

        // Low Stock Books (available between 1 and 2 copies)
        $lowStockBooks = Book::with('category:id,name')
            ->where('available_quantity', '>', 0)
            ->where('available_quantity', '<=', 2)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'isbn' => $book->isbn,
                    'category_name' => $book->category?->name ?? 'General',
                    'quantity' => $book->quantity,
                    'available_quantity' => $book->available_quantity,
                ];
            });

        // 4. Graphs: Borrowing & Returning Trends (last 6 months)
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::today()->subMonths($i);
            $year = $monthDate->year;
            $month = $monthDate->month;
            $label = $monthDate->format('M Y');

            $borrowedCount = Borrowing::whereYear('borrowed_at', $year)
                ->whereMonth('borrowed_at', $month)
                ->count();

            $returnedCount = Borrowing::whereYear('returned_at', $year)
                ->whereMonth('returned_at', $month)
                ->count();

            $trends[] = [
                'label' => $label,
                'borrowed' => $borrowedCount,
                'returned' => $returnedCount,
            ];
        }

        // 5. Graphs: Category Distribution
        $categoryDistribution = Category::withCount('books')
            ->get()
            ->map(function ($c) {
                return [
                    'name' => $c->name,
                    'books_count' => $c->books_count,
                ];
            })
            ->filter(fn ($c) => $c['books_count'] > 0)
            ->values();

        // 6. Recent Borrowings Activity
        $recentQuery = Borrowing::with(['user:id,name,email', 'book:id,title,author'])
            ->latest();

        if (!$isStaff) {
            $recentQuery->where('user_id', $user->id);
        }

        $recentBorrowings = $recentQuery->take(6)->get()->map(function ($b) use ($today) {
            $isOverdue = $b->status === 'borrowed' && Carbon::parse($b->due_date)->lt($today);
            return [
                'id' => $b->id,
                'user_name' => $b->user?->name ?? 'Unknown',
                'user_email' => $b->user?->email ?? '',
                'book_title' => $b->book?->title ?? 'Untitled',
                'book_id' => $b->book_id,
                'borrowed_at' => $b->borrowed_at ? Carbon::parse($b->borrowed_at)->format('M d, Y') : '-',
                'due_date' => $b->due_date ? Carbon::parse($b->due_date)->format('M d, Y') : '-',
                'returned_at' => $b->returned_at ? Carbon::parse($b->returned_at)->format('M d, Y') : null,
                'status' => $b->status,
                'is_overdue' => $isOverdue,
            ];
        });

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'memberStats' => $memberStats,
            'popularBooks' => $popularBooks,
            'outOfStockBooks' => $outOfStockBooks,
            'lowStockBooks' => $lowStockBooks,
            'trends' => $trends,
            'categoryDistribution' => $categoryDistribution,
            'recentBorrowings' => $recentBorrowings,
            'isStaff' => $isStaff,
        ]);
    }
}
