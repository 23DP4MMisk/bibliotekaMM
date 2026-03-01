<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\NodalaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserBookController;

Route::post('/register', [AuthController::class, 'register']); 
Route::post('/reģistrēties', [AuthController::class, 'register']);
Route::post('/pieslēgties', [AuthController::class, 'login']);
Route::get('/check-auth', [AuthController::class, 'checkAuth']);


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

Route::get('/jwt-test', function() {
    try {
        // Попробуем создать тестового пользователя прямо здесь
        $user = App\Models\Lietotajs::where('epasts', 'test@test.com')->first();
        
        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден']);
        }
        
        $token = Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        
        return response()->json([
            'success' => true,
            'message' => 'JWT работает',
            'token' => $token,
            'user' => $user->epasts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

Route::get('/test-token', function(Request $request) {
    $token = $request->header('Authorization');
    $user = auth()->user();
    
    return response()->json([
        'header_token' => $token,
        'user' => $user ? $user->epasts : null,
        'authenticated' => auth()->check(),
        'headers' => $request->headers->all()
    ]);
})->middleware('auth:api');


Route::get('/books', [BookController::class, 'index']);
Route::get('/books/search/{query}', [BookController::class, 'search']);
Route::get('/homepage-books', [BookController::class, 'homepage']);
Route::get('/books/{isbn}', [BookController::class, 'show']);

Route::get('/genres', [BookController::class, 'genres']);

Route::get('/nodalas', [NodalaController::class, 'index']);
Route::get('/nodalas/{id}/books', [NodalaController::class, 'books']);


Route::post('/izrakstīties', [AuthController::class, 'logout']);
Route::get('/user/books', [UserBookController::class, 'index']);
Route::post('/user/books/add', [UserBookController::class, 'add']);
Route::put('/user/book/status', [UserBookController::class, 'updateStatus']);
Route::delete('/user/book/{id}', [UserBookController::class, 'destroy']);



