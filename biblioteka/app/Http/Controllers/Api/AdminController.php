<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lietotajs;
use App\Models\Gramata;
use App\Models\Zanrs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Pārbauda vai lietotājs ir administrators
     */
    private function checkAdmin($request)
    {
        $user = $request->user();
        if (!$user || $user->loma !== 'admins') {
            Log::warning('checkAdmin failed', [
                'user_exists' => !!$user,
                'loma' => $user ? $user->loma : null
            ]);
            return false;
        }
        return true;
    }

    /**
     * Iegūt visus lietotājus
     */
  
    public function getUsers(Request $request)
    {
        // Pārbaudām Authorization header
        $authHeader = $request->header('Authorization');
        \Log::info('Auth header: ' . $authHeader);
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'Nav Authorization header',
                'your_loma' => null
            ], 401);
        }
        
        $token = str_replace('Bearer ', '', $authHeader);
        \Log::info('Token: ' . $token);
        
        // PARSĒJAM TOKENU - pieņemot, ka formāts ir "kodsID_timestamp"
        $tokenParts = explode('_', $token);
        $userId = $tokenParts[0] ?? null;
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Nederīgs tokena formāts',
                'your_loma' => null
            ], 401);
        }
        
        // Atrodam lietotāju pēc ID
        $user = \App\Models\Lietotajs::where('kodsID', $userId)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Lietotājs nav atrasts',
                'your_loma' => null
            ], 401);
        }
        
        // Pārbaudām vai lietotājs ir aktīvs
        if ($user->status !== 'aktivs') {
            return response()->json([
                'success' => false,
                'message' => 'Lietotāja konts nav aktīvs',
                'your_loma' => $user->loma
            ], 403);
        }
        
        // Pārbaudām vai loma ir 'admins'
        if ($user->loma !== 'admins') {
            return response()->json([
                'success' => false,
                'message' => 'Piekļuve liegta. Nepieciešama administratora tiesības.',
                'your_loma' => $user->loma
            ], 403);
        }
        
        // Viss ok - ielādējam lietotājus
        try {
            $users = \App\Models\Lietotajs::select('kodsID', 'lietotaja_vards', 'epasts', 'loma', 'status', 'registresanas_datums')
                ->orderBy('registresanas_datums', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users,
                'count' => count($users),
                'admin' => $user->lietotaja_vards
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getUsers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot lietotājus: ' . $e->getMessage(),
                'your_loma' => $user->loma
            ], 500);
        }
    }
    

    /**
     * Atjaunināt lietotāja statusu
     */
    public function updateUserStatus(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Piekļuve liegta'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:aktivs,blokets'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Lietotajs::where('kodsID', $id)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lietotājs nav atrasts'
                ], 404);
            }

            $user->update(['status' => $request->status]);
            
            return response()->json([
                'success' => true,
                'message' => 'Lietotāja statuss veiksmīgi atjaunināts',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pievienot jaunu grāmatu
     */
    public function storeBook(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Piekļuve liegta'], 403);
        }

        $validator = Validator::make($request->all(), [
            'ISBN' => 'required|integer|unique:Gramata,ISBN',
            'nosaukums' => 'required|string|max:50',
            'autors' => 'required|string|max:255',
            'gads' => 'nullable|string|size:4',
            'lapu_skaits' => 'nullable|integer|min:1',
            'apraksts' => 'nullable|string|max:1000',
            'Zanra_ID' => 'required|integer|exists:Zanrs,Zanra_ID',
            'Nodala_ID' => 'required|integer|exists:Nodala,Nodala_ID',
            'faila_pdf' => 'nullable|string|max:250',
            'vaku_attels' => 'nullable|string|max:250'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $book = Gramata::create($request->all());
            
            // Atjauninām žanra grāmatu skaitu
            $this->updateGenreBookCount($request->Zanra_ID);

            return response()->json([
                'success' => true,
                'message' => 'Grāmata veiksmīgi pievienota',
                'data' => $book
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rediģēt grāmatu
     */
    public function updateBook(Request $request, $isbn)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Piekļuve liegta'], 403);
        }

        $book = Gramata::where('ISBN', $isbn)->first();
        
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Grāmata nav atrasta'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nosaukums' => 'sometimes|required|string|max:50',
            'autors' => 'sometimes|required|string|max:255',
            'gads' => 'nullable|string|size:4',
            'lapu_skaits' => 'nullable|integer|min:1',
            'apraksts' => 'nullable|string|max:1000',
            'Zanra_ID' => 'sometimes|required|integer|exists:Zanrs,Zanra_ID',
            'Nodala_ID' => 'sometimes|required|integer|exists:Nodala,Nodala_ID',
            'faila_pdf' => 'nullable|string|max:250',
            'vaku_attels' => 'nullable|string|max:250'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $oldGenreId = $book->Zanra_ID;
            $book->update($request->all());

            if ($oldGenreId != $book->Zanra_ID) {
                $this->updateGenreBookCount($oldGenreId);
                $this->updateGenreBookCount($book->Zanra_ID);
            } else {
                $this->updateGenreBookCount($book->Zanra_ID);
            }

            return response()->json([
                'success' => true,
                'message' => 'Grāmata veiksmīgi atjaunota',
                'data' => $book
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dzēst grāmatu
     */
    public function deleteBook(Request $request, $isbn)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Piekļuve liegta'], 403);
        }

        $book = Gramata::where('ISBN', $isbn)->first();
        
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Grāmata nav atrasta'], 404);
        }

        try {
            $genreId = $book->Zanra_ID;
            $book->delete();
            
            $this->updateGenreBookCount($genreId);
            
            return response()->json([
                'success' => true,
                'message' => 'Grāmata veiksmīgi dzēsta'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pievienot jaunu žanru
     */
    public function storeGenre(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Piekļuve liegta'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nosaukums' => 'required|string|max:40|unique:Zanrs,nosaukums',
            'Nodala' => 'required|integer|exists:Nodala,Nodala_ID'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $genre = Zanrs::create([
                'nosaukums' => $request->nosaukums,
                'Nodala' => $request->Nodala,
                'gramatu_skaits' => 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Žanrs veiksmīgi pievienots',
                'data' => $genre
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rediģēt žanru
     */
    public function updateGenre(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Piekļuve liegta'], 403);
        }

        $genre = Zanrs::where('Zanra_ID', $id)->first();
        
        if (!$genre) {
            return response()->json(['success' => false, 'message' => 'Žanrs nav atrasts'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nosaukums' => 'sometimes|required|string|max:40|unique:Zanrs,nosaukums,' . $id . ',Zanra_ID',
            'Nodala' => 'sometimes|required|integer|exists:Nodala,Nodala_ID'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $genre->update($request->only(['nosaukums', 'Nodala']));
            
            return response()->json([
                'success' => true,
                'message' => 'Žanrs veiksmīgi atjaunots',
                'data' => $genre
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dzēst žanru
     */
    public function deleteGenre(Request $request, $id)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Piekļuve liegta'], 403);
        }

        $genre = Zanrs::where('Zanra_ID', $id)->first();
        
        if (!$genre) {
            return response()->json(['success' => false, 'message' => 'Žanrs nav atrasts'], 404);
        }

        $booksCount = Gramata::where('Zanra_ID', $id)->count();
        
        if ($booksCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nevar dzēst žanru, kam piesaistītas grāmatas'
            ], 409);
        }

        try {
            $genre->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Žanrs veiksmīgi dzēsts'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Grāmatas statistika
     */
    public function bookStats(Request $request, $isbn)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Piekļuve liegta'], 403);
        }

        $book = Gramata::with(['zanrs', 'nodala'])
            ->where('ISBN', $isbn)
            ->first();
        
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Grāmata nav atrasta'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'book' => [
                    'ISBN' => $book->ISBN,
                    'nosaukums' => $book->nosaukums,
                    'autors' => $book->autors,
                    'gads' => $book->gads
                ],
                'genre' => $book->zanrs->nosaukums ?? 'Nav',
                'section_id' => $book->Nodala_ID,
                'added_date' => $book->created_at ? $book->created_at->format('Y-m-d H:i:s') : null,
                'last_updated' => $book->updated_at ? $book->updated_at->format('Y-m-d H:i:s') : null
            ]
        ]);
    }

    /**
     * Lietotāju statistika
     */
    public function userStats(Request $request)
    {
        if (!$this->checkAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Piekļuve liegta'], 403);
        }

        $stats = [
            'total_users' => Lietotajs::count(),
            'active_users' => Lietotajs::where('status', 'aktivs')->count(),
            'blocked_users' => Lietotajs::where('status', 'blokets')->count(),
            'admins' => Lietotajs::where('loma', 'admins')->count(),
            'registered_clients' => Lietotajs::where('loma', 'registretajsklients')->count(),
            'guests' => Lietotajs::where('loma', 'viesis')->count(),
            'recent_registrations' => Lietotajs::where('registresanas_datums', '>=', now()->subDays(30))->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Palīgfunkcija žanra grāmatu skaita atjaunināšanai
     */
    private function updateGenreBookCount($genreId)
    {
        if (!$genreId) return;
        $count = Gramata::where('Zanra_ID', $genreId)->count();
        Zanrs::where('Zanra_ID', $genreId)->update(['gramatu_skaits' => $count]);
    }
}