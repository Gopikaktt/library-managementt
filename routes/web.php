<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowingController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/home', function () {
    return Inertia::render('Home');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

    Route::get('/books/{book}/edit', [BookController::class, 'edit'])
        ->middleware('permission:edit books');

    Route::put('/books/{book}', [BookController::class, 'update'])
        ->middleware('permission:edit books');

    Route::delete('/books/{book}', [BookController::class, 'destroy'])
        ->middleware('permission:delete books');

        // Borrowings & Issues
    Route::get('/borrowings', [BorrowingController::class, 'index'])
        ->middleware('permission:view borrowing history');

    Route::get('/borrowings/create', [BorrowingController::class, 'create'])
        ->middleware('permission:issue books');

    Route::post('/borrowings', [BorrowingController::class, 'store'])
        ->middleware('permission:issue books');

    Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnBook'])
        ->middleware('permission:return books');

});

require __DIR__.'/auth.php';
