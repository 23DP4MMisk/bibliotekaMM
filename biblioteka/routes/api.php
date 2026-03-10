<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\NodalaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserBookController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;

Route::post('/register', [AuthController::class, 'register']); 
Route::post('/reģistrēties', [AuthController::class, 'register']);
Route::post('/pieslēgties', [AuthController::class, 'login']);
Route::get('/check-auth', [AuthController::class, 'checkAuth']);

Route::post('/reviews', [ReviewController::class, 'store']);
Route::get('/reviews/check/{bookId}', [ReviewController::class, 'check']);
Route::get('/books/{isbn}/reviews', [ReviewController::class, 'bookReviews']);

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


Route::get('/fix-genre-counts', function() {
    try {
        $genres = DB::table('Zanrs')->get();
        $updated = 0;
        
        foreach ($genres as $genre) {
            $count = DB::table('Gramata')
                ->where('Zanra_ID', $genre->Zanra_ID)
                ->count();
            
            DB::table('Zanrs')
                ->where('Zanra_ID', $genre->Zanra_ID)
                ->update(['gramatu_skaits' => $count]);
            
            $updated++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Atjaunināti {$updated} žanri",
            'data' => DB::table('Zanrs')->get()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Kļūda: ' . $e->getMessage()
        ], 500);
    }
});


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


    
Route::prefix('admin')->group(function () {
    // Lietotāju pārvaldība
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::put('/users/{id}/status', [AdminController::class, 'updateUserStatus']);
    
    // Grāmatu pārvaldība
    Route::post('/books', [AdminController::class, 'storeBook']);
    Route::put('/books/{isbn}', [AdminController::class, 'updateBook']);
    Route::delete('/books/{isbn}', [AdminController::class, 'deleteBook']);
    
    // Žanru pārvaldība
    Route::post('/genres', [AdminController::class, 'storeGenre']);
    Route::put('/genres/{id}', [AdminController::class, 'updateGenre']);
    Route::delete('/genres/{id}', [AdminController::class, 'deleteGenre']);
    
    // Statistika
    Route::get('/stats/books/{isbn}', [AdminController::class, 'bookStats']);
    Route::get('/stats/users', [AdminController::class, 'userStats']);
});

// DEBUG MARŠRUTS - lai redzētu lietotāja datus no tokena
Route::get('/debug-user-from-token', function(Request $request) {
    $authHeader = $request->header('Authorization');
    
    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        return response()->json([
            'success' => false,
            'message' => 'Nav Authorization header',
            'headers' => $request->headers->all()
        ]);
    }
    
    $token = str_replace('Bearer ', '', $authHeader);
    
    // Parsējam tokenu (pieņemot, ka formāts ir "kodsID_timestamp")
    $tokenParts = explode('_', $token);
    $userId = $tokenParts[0] ?? null;
    
    if (!$userId) {
        return response()->json([
            'success' => false,
            'message' => 'Nederīgs tokena formāts',
            'token' => $token
        ]);
    }
    
    $user = DB::table('Lietotajs')->where('kodsID', $userId)->first();
    
    return response()->json([
        'success' => true,
        'token' => $token,
        'user_id_from_token' => $userId,
        'user_from_db' => $user,
        'is_admin' => $user && $user->loma === 'admins'
    ]);
});
    


// routes/api.php - pievienojiet šo maršrutu
Route::get('/debug-token', function(Request $request) {
    $authHeader = $request->header('Authorization');
    
    $result = [
        'has_auth_header' => !is_null($authHeader),
        'auth_header' => $authHeader,
        'headers' => $request->headers->all(),
    ];
    
    if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
        $token = str_replace('Bearer ', '', $authHeader);
        $tokenParts = explode('_', $token);
        $userId = $tokenParts[0] ?? null;
        
        $result['token'] = $token;
        $result['user_id_from_token'] = $userId;
        
        if ($userId) {
            $user = DB::table('Lietotajs')->where('kodsID', $userId)->first();
            $result['user_from_db'] = $user;
        }
    }
    
    return response()->json($result);
});