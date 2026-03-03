<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Gramata;
use App\Models\Nodala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function genres()
    {
        try {
            $genres = DB::table('Zanrs')->get();
            
            return response()->json([
                'success' => true,
                'data' => $genres
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot žanrus',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }




    // GET /api/books — visas grāmatas
    public function index()
    {
        try {
            $books = Gramata::with('nodala')->get();

            return response()->json([
                'success' => true,
                'count' => $books->count(),
                'data' => $books->map(function($book) {
                    return [
                        'isbn' => $book->ISBN,
                        'nosaukums' => $book->nosaukums,
                        'autors' => $book->autors,
                        'gads' => $book->gads,
                        'lapu_skaits' => $book->lapu_skaits,
                        'vaku_attels' => $book->vaku_attels,
                        'apraksts' => $book->apraksts,
                        'faila_pdf' => $book->faila_pdf,
                        'nodala_id' => $book->Nodala_ID,
                        'zanra_id' => $book->Zanra_ID,
                        'category' => [
                            'id' => $book->Nodala_ID,
                            'tips' => $book->nodala->tips ?? null,
                        ],
                        'created_at' => $book->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $book->updated_at->format('Y-m-d H:i:s'),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot grāmatas',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

      public function search($query)
    {
        try {
            $books = Gramata::where('nosaukums', 'like', '%' . $query . '%')
                ->orWhere('autors', 'like', '%' . $query . '%')
                ->orWhere('ISBN', 'like', '%' . $query . '%')
                ->with('nodala')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $books->count(),
                'data' => $books->map(function($book) {
                    return [
                        'isbn' => $book->ISBN,
                        'nosaukums' => $book->nosaukums,
                        'autors' => $book->autors,
                        'gads' => $book->gads,
                        'lapu_skaits' => $book->lapu_skaits,
                        'vaku_attels' => $book->vaku_attels,
                        'nodala_id' => $book->Nodala_ID,
                        'category' => [
                            'id' => $book->Nodala_ID,
                            'tips' => $book->nodala->tips ?? null,
                        ],
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda meklējot grāmatas',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // GET /api/books/{isbn} — konkreta gramata
    public function show($isbn, Request $request)
    {
        try {
            $book = Gramata::with('nodala')->find($isbn);

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grāmata ar ISBN ' . $isbn . ' nav atrasta'
                ], 404);
            }

            $inLibrary = false;
            $bookStatus = null;

            $user = $this->getUserFromToken($request);

            \Log::info('=== BOOK SHOW DEBUG ===');
            \Log::info('ISBN: ' . $isbn);
            \Log::info('User from token: ' . ($user ? 'YES (ID: ' . $user->kodsID . ')' : 'NO'));

            if ($user) {
                 
                
                $userBook = DB::table('LietotajGramatas')
                    ->where('Lietotajs', $user->kodsID)
                    ->where('Gramatas', $isbn)
                    ->first();

                    \Log::info('Book in library: ' . ($userBook ? 'YES' : 'NO'));
                    
                if ($userBook) {
                    $inLibrary = true;
                    $bookStatus = $userBook->statuss;
                    \Log::info('Book status: ' . $bookStatus);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'isbn' => $book->ISBN,
                    'nosaukums' => $book->nosaukums,
                    'autors' => $book->autors,
                    'gads' => $book->gads,
                    'lapu_skaits' => $book->lapu_skaits,
                    'apraksts' => $book->apraksts,
                    'faila_pdf' => $book->faila_pdf,
                    'vaku_attels' => $book->vaku_attels,
                    'in_library' => $inLibrary,    
                    'book_status' => $bookStatus, 
                    'category' => [
                        'id' => $book->Nodala_ID,
                        'tips' => $book->nodala->tips ?? null,
                    ],
                    'created_at' => $book->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $book->updated_at->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot grāmatu',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

      // GET /api/homepage-books — gramatas galvenai lapai
    public function homepage()
    {
        try {
            // Piemeram 6 pedejas gramatas
            $books = Gramata::with('nodala')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            return response()->json([
                'success' => true,
                'count' => $books->count(),
                'data' => $books->map(function($book) {
                    return [
                        'isbn' => $book->ISBN,
                        'nosaukums' => $book->nosaukums,
                        'autors' => $book->autors,
                        'vaku_attels' => $book->vaku_attels,
                        'nodala_id' => $book->Nodala_ID,
                        'category' => [
                            'id' => $book->Nodala_ID,
                            'tips' => $book->nodala->tips ?? null,
                        ],
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot grāmatas galvenajai lapai',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function getUserFromToken($request)
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return null;
        }
        
        $token = str_replace('Bearer ', '', $token);
        $parts = explode('_', $token);
        
        if (count($parts) === 2) {
            $userId = $parts[0];
            return \App\Models\Lietotajs::find($userId);
        }
        
        return null;
    }

}
