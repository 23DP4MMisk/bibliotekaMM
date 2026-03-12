<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Gramata;
use App\Models\Nodala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Lejupielade;
use App\Models\Parskata;

class BookController extends Controller
{


  
    public function incrementDownload(Request $request,$isbn) {
            $book = Gramata::where('ISBN', $isbn)->first();
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Grāmata nav atrasta'], 404);
        }

       
        $user = $this->getUserFromToken($request);
        $userId = $user ? $user->kodsID : null;

        \Log::info('📥 Download tracked', [
            'isbn' => $isbn,
            'user_id' => $userId,
            'date' => now()->toDateString()
        ]);
        try {
            Lejupielade::create([
                'Datums' => now()->toDateString(),
                'Gramatas_ID' => $isbn,
                'Lietotaja_ID' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lejupielāde reģistrēta'
            ]);
        } catch (\Exception $e) {
            \Log::error('Download error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kļūda reģistrējot lejupielādi'
            ], 500);
        }
    }
    

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
                        'views' => Parskata::where('Gramatas', $book->ISBN)->sum('parskatas_skaits'),
                        'downloads' => Lejupielade::where('Gramatas_ID', $book->ISBN)->count(),
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
                        'views' => Parskata::where('Gramatas', $book->ISBN)->sum('parskatas_skaits'),
                        'downloads' => Lejupielade::where('Gramatas_ID', $book->ISBN)->count(),
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
            $book = Gramata::find($isbn);

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

            
            try {
            $existing = Parskata::where('Gramatas', $isbn)->first();
            if ($existing) {
                $existing->increment('parskatas_skaits');
                \Log::info('Views incremented');
            } else {
                Parskata::create([
                    'parskatas_skaits' => 1,
                    'Gramatas' => $isbn,
                    'Lietotajs' => $user ? $user->kodsID : null, 
                ]);
                \Log::info('New views record created with Lietotajs: ' . ($user ? $user->kodsID : 'NULL'));
            }
            } catch (\Exception $viewError) {
                \Log::error('Error recording views: ' . $viewError->getMessage());
                
            }

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
            
            
            try {
                $book->load('nodala');
            } catch (\Exception $nodeError) {
                \Log::error('Error loading nodala: ' . $nodeError->getMessage());
                
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
        $authHeader = $request->header('Authorization');
        \Log::info('Auth header: ' . ($authHeader ? 'YES' : 'NO'));
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            \Log::info('Invalid or missing Bearer token');
            return null;
        }
        
        $token = str_replace('Bearer ', '', $authHeader);
        \Log::info('Token: ' . substr($token, 0, 20) . '...');
        
        
        $tokenParts = explode('_', $token);
        $userId = $tokenParts[0] ?? null;

        \Log::info('Parsed user ID from token: ' . ($userId ?? 'NULL'));
        
        if (!$userId) {
            \Log::info('Could not parse user ID from token');
            return null;
        }
        
        
        $user = \App\Models\Lietotajs::where('kodsID', $userId)->first();
        
        if (!$user) {
            \Log::info('User not found in database: ' . $userId);
            return null;
        }
        
        \Log::info('User found: ' . $user->lietotaja_vards);
        return $user;

    }

}
