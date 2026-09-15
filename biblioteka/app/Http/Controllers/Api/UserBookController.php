<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lietotajs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserBookController extends Controller
{
    /**
     * Iegūst lietotāju no Bearer token
     * Kas dara: Atgriež lietotāju, ja token ir derīgs
     * Kad izmantojas: Kad nepieciešams pārbaudīt lietotāja autentifikāciju
     * Tokena formats: 'ID_laiks'
    */
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
            return Lietotajs::find($userId);
        }
        
        return null;
    }
    
    /**
     * Pievieno grāmatu lietotāja bibliotēkai
     * Kas dara: Pievieno grāmatu lietotāja bibliotēkai ar statusu
     * Kad izmantojas: Kad lietotājs vēlas pievienot grāmatu savai bibliotēkai
    */
    public function add(Request $request)
    {

        Log::info('ADD BOOK TO LIBRARY');
        Log::info('Request data:', $request->all());
        Log::info('Headers:', $request->headers->all());
        
        try {
            $user = $this->getUserFromToken($request);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nav autentificēts'
                ], 401);
            }
            
            $validator = Validator::make($request->all(), [
                'isbn' => 'required',
                'statuss' => 'required|in:lasu,izlasiju,vel nelasiju'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            
            $existing = DB::table('LietotajGramatas')
                ->where('Lietotajs', $user->kodsID)
                ->where('Gramatas', $request->isbn)
                ->first();
                
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Šī grāmata jau ir jūsu bibliotēkā'
                ], 400);
            }
            
            DB::table('LietotajGramatas')->insert([
                'Lietotajs' => $user->kodsID,
                'Gramatas' => $request->isbn,
                'statuss' => $request->statuss,
                'pievienosanas_datums' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Grāmata pievienota bibliotēkai'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda pievienojot grāmatu'
            ], 500);
        }
    }
    
    /**
     * Iegūst lietotāja grāmatas
     * Kas dara: Atgriež visas gramatas, kuras lietotajs pievienoja sava biblioteka
     * Kad izmanto: Timekļa lapa 'Mana biblioteka'
     */
    public function index(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nav autentificēts'
                ], 401);
            }
            
            $books = DB::table('LietotajGramatas')
                ->join('Gramata', 'LietotajGramatas.Gramatas', '=', 'Gramata.ISBN')
                ->where('LietotajGramatas.Lietotajs', $user->kodsID)
                ->select(
                    'LietotajGramatas.*',
                    'Gramata.nosaukums',
                    'Gramata.autors',
                    'Gramata.vaku_attels',
                    'Gramata.faila_pdf',
                    'Gramata.ISBN as gramatas_id'
                )
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $books
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot grāmatas'
            ], 500);
        }
    }
    
    /**
     * Atjaunina lietotāja grāmatas statusu
     * Kas dara: Atjaunina statusu grāmatai lietotāja bibliotēkā
     * Kad izmanto: Kad lietotājs maina statusu grāmatai (lasu, izlasiju, vel nelasiju)
     */
    public function updateStatus(Request $request)
    {
        Log::info('UPDATE BOOK STATUS');
        Log::info('Request data:', $request->all());
        
        try {
            $user = $this->getUserFromToken($request);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nav autentificēts'
                ], 401);
            }
            
            $validator = Validator::make($request->all(), [
                'book_id' => 'required',
                'status' => 'required|in:lasu,izlasiju,vel nelasiju'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            Log::info('Updating book ID: ' . $request->book_id);
            Log::info('New status: ' . $request->status);
            
            $updated = DB::table('LietotajGramatas')
                ->where('LietotajGramatas_ID', $request->book_id)
                ->where('Lietotajs', $user->kodsID)
                ->update([
                    'statuss' => $request->status,
                    'updated_at' => now()
                ]);
            
            Log::info('Updated rows: ' . $updated);

             if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Statuss atjaunināts'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Grāmata nav atrasta'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Servera kļūda: ' . $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Dzēš grāmatu no lietotāja bibliotēkas
     * Kas dara: Dzēš grāmatu no lietotāja bibliotēkas pēc ID
     * Kad izmanto: Kad lietotājs vēlas dzēst grāmatu
     */
    public function destroy($id, Request $request)
   {
    
    
    try {
        $user = $this->getUserFromToken($request);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Nav autentificēts'
            ], 401);
        }
        
        Log::info('User ID: ' . $user->kodsID);
        
        
        $exists = DB::table('LietotajGramatas')
            ->where('LietotajGramatas_ID', $id)
            ->where('Lietotajs', $user->kodsID)
            ->exists();
            
        if (!$exists) {
            Log::warning('Book not found in user library');
            return response()->json([
                'success' => false,
                'message' => 'Grāmata nav atrasta jūsu bibliotēkā'
            ], 404);
        }
        
        
        $deleted = DB::table('LietotajGramatas')
            ->where('LietotajGramatas_ID', $id)
            ->where('Lietotajs', $user->kodsID)
            ->delete();
        
        Log::info('Deleted rows: ' . $deleted);
        
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Grāmata veiksmīgi dzēsta'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Neizdevās dzēst grāmatu'
            ], 500);
        }
        
    } catch (\Exception $e) {
        Log::error('Error deleting book: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Servera kļūda: ' . $e->getMessage()
        ], 500);
    }
  }
}