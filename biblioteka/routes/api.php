<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\NodalaController;



// ============ TEST маршруты ============
Route::get('/test', function() {
    return response()->json([
        'status' => 'success',
        'message' => 'API darbojas!',
        'timestamp' => now(),
        'endpoints' => [
            '/api/books' => 'GET - Visas grāmatas',
            '/api/books/search/{query}' => 'GET - Meklēt grāmatas',
            '/api/homepage-books' => 'GET - Grāmatas galvenajai lapai',
            '/api/nodalas' => 'GET - Visas nodaļas',
            '/api/nodalas/{id}/books' => 'GET - Grāmatas nodaļā'
        ]
    ]);
});

// ============ КНИГИ (BookController) ============
Route::get('/books', [BookController::class, 'index']);                 // Visas grāmatas
Route::get('/books/search/{query}', [BookController::class, 'search']); // Meklēt grāmatas
Route::get('/homepage-books', [BookController::class, 'homepage']);     // Grāmatas galvenajai lapai
Route::get('/books/{isbn}', [BookController::class, 'show']);           // Konkrēta grāmata

// ============ ОТДЕЛЫ (NodalaController) ============
Route::get('/nodalas', [NodalaController::class, 'index']);             // Visas nodaļas
Route::get('/nodalas/{id}/books', [NodalaController::class, 'books']);  // Grāmatas nodaļā