<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nodala;
use Illuminate\Http\Request;

class NodalaController extends Controller
{
    // GET /api/nodalas — все отделы
    public function index()
    {
        try {
            $nodalas = Nodala::all();

            return response()->json([
                'success' => true,
                'count' => $nodalas->count(),
                'data' => $nodalas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot nodaļas',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // GET /api/nodalas/{id}/books — книги в отделе
    public function books($id)
    {
        try {
            $nodala = Nodala::with('gramatas')->find($id);

            if (!$nodala) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nodaļa ar ID ' . $id . ' nav atrasta'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'nodala' => $nodala->tips,
                    'gramatas' => $nodala->gramatas->map(function($book) {
                        return [
                            'isbn' => $book->ISBN,
                            'nosaukums' => $book->nosaukums,
                            'autors' => $book->autors,
                            'gads' => $book->gads,
                            'lapu_skaits' => $book->lapu_skaits,
                            'vaku_attels' => $book->vaku_attels,
                        ];
                    }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot grāmatas nodaļā',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function allBooks()
    {
        try {
            $gramatas = Gramata::with('nodala')->get();

            return response()->json([
                'success' => true,
                'count' => $gramatas->count(),
                'data' => $gramatas->map(function($book) {
                    return [
                        'isbn' => $book->ISBN,
                        'nosaukums' => $book->nosaukums,
                        'autors' => $book->autors,
                        'gads' => $book->gads,
                        'lapu_skaits' => $book->lapu_skaits,
                        'vaku_attels' => $book->vaku_attels,
                        'nodala_id' => $book->Nodala_ID,
                        'nodala' => $book->nodala ? [
                            'id' => $book->nodala->id,
                            'tips' => $book->nodala->tips
                        ] : null
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot visas grāmatas',
                'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 500);
        }
    }

     public function search($query)
    {
        try {
            $gramatas = Gramata::where('nosaukums', 'like', '%' . $query . '%')
                ->orWhere('autors', 'like', '%' . $query . '%')
                ->orWhere('ISBN', 'like', '%' . $query . '%')
                ->with('nodala')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $gramatas->count(),
                'data' => $gramatas->map(function($book) {
                    return [
                        'isbn' => $book->ISBN,
                        'nosaukums' => $book->nosaukums,
                        'autors' => $book->autors,
                        'gads' => $book->gads,
                        'lapu_skaits' => $book->lapu_skaits,
                        'vaku_attels' => $book->vaku_attels,
                        'nodala_id' => $book->Nodala_ID,
                        'nodala' => $book->nodala ? [
                            'id' => $book->nodala->id,
                            'tips' => $book->nodala->tips
                        ] : null
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

     public function allGramatas()
    {
        return $this->allBooks();
    }
}

