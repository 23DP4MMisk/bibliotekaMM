<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gramata;
use App\Models\Nodala;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // GET /api/books — все книги
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

    // GET /api/books/{isbn} — конкретная книга
    public function show($isbn)
    {
        try {
            $book = Gramata::with('nodala')->find($isbn);

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grāmata ar ISBN ' . $isbn . ' nav atrasta'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'isbn' => $book->ISBN,
                    'nosaukums' => $book->nosaukums,
                    'autors' => $book->autors,
                    'gads' => $book->gads,
                    'lapu_skaits' => $book->lapu_skaits,
                    'vaku_attels' => $book->vaku_attels,
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

      // GET /api/homepage-books — книги для главной страницы
    public function homepage()
    {
        try {
            // Например, последние 6 книг
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
}
