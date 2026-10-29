<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

use App\Http\Controllers\DashboardController;

Route::get('/home', function () {
    return Inertia::render('Home');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route::resource('books', BookController::class);
    
});

Route::middleware('auth')->group(function () {
    Route::get('/books', [BookController::class, 'index'])
        ->middleware('permission:view books');

    Route::get('/books/create', [BookController::class, 'create'])
        ->middleware('permission:create books');

    Route::post('/books', [BookController::class, 'store'])
        ->middleware('permission:create books');

    Route::get('/books/{book}', [BookController::class, 'show'])
        ->middleware('permission:view books');

    Route::get('/books/{book}/edit', [BookController::class, 'edit'])
        ->middleware('permission:edit books');

    Route::put('/books/{book}', [BookController::class, 'update'])
        ->middleware('permission:edit books');

    Route::delete('/books/{book}', [BookController::class, 'destroy'])
        ->middleware('permission:delete books');

    // Member book ordering
    Route::post('/books/{book}/order', [BorrowingController::class, 'order'])
        ->middleware('permission:view books');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])
        ->middleware('permission:view books');

    Route::post('/categories', [CategoryController::class, 'store'])
        ->middleware('permission:create books');

    Route::get('/categories/{category}', [CategoryController::class, 'show'])
        ->middleware('permission:view books');

    Route::put('/categories/{category}', [CategoryController::class, 'update'])
        ->middleware('permission:edit books');

    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->middleware('permission:delete books');

    // Borrowings & Issues
    Route::get('/borrowings', [BorrowingController::class, 'index'])
        ->middleware('permission:view borrowing history');

    Route::get('/borrowings/create', [BorrowingController::class, 'create'])
        ->middleware('permission:issue books');

    Route::post('/borrowings', [BorrowingController::class, 'store'])
        ->middleware('permission:issue books');

    Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show'])
        ->middleware('permission:view borrowing history');

    Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnBook'])
        ->middleware('permission:return books');

    // User & Permission Management (Admin)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

});

require __DIR__.'/auth.php';
