<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\NodalaController;
use App\Http\Controllers\Api\AuthController;


Route::middleware('web')->group(function (){
Route::post('/register', [AuthController::class, 'register']); 
Route::post('/reģistrēties', [AuthController::class, 'register']);
Route::post('/pieslēgties', [AuthController::class, 'login']);
Route::post('/izrakstīties', [AuthController::class, 'logout']);
Route::get('/check-auth', [AuthController::class, 'checkAuth']);
});


Route::get('/test-create-user', [AuthController::class, 'testCreateUser']);
Route::get('/test-db', function() {
    try {
        $count = DB::table('Lietotajs')->count();
        return response()->json([
            'success' => true,
            'users_count' => $count,
            'database' => 'Connected'
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});


Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API darbojas!',
        'timestamp' => now()
    ]);
});


Route::get('/books', [BookController::class, 'index']);
Route::get('/books/search/{query}', [BookController::class, 'search']);
Route::get('/homepage-books', [BookController::class, 'homepage']);
Route::get('/books/{isbn}', [BookController::class, 'show']);

Route::get('/genres', [BookController::class, 'genres']);

Route::get('/nodalas', [NodalaController::class, 'index']);
Route::get('/nodalas/{id}/books', [NodalaController::class, 'books']);


