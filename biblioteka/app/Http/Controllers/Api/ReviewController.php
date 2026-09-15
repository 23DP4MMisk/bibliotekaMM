<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lietotajs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
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
     * Iegūst grāmatas atsauksmes
     * Kas dara: Atgriež atsauksmes par grāmatu, kā arī lietotāja vārdu 
     * Kad izmantojas: Kad lietotājs apskata grāmatu un vēlas redzēt atsauksmes
    */
    public function bookReviews($isbn)
    {
        try {
            $allReviews = DB::table('Atsauksmes')
                ->join('Lietotajs', 'Atsauksmes.Lietotaja_ID', '=', 'Lietotajs.kodsID')
                ->leftJoin('Atsauksmes as parent', 'Atsauksmes.vecakais_komentars', '=', 'parent.Atsauksmes_ID')
                ->leftJoin('Lietotajs as parent_user', 'parent.Lietotaja_ID', '=', 'parent_user.kodsID')
                ->where('Atsauksmes.Gramatas_ID', $isbn)
                ->select(
                    'Atsauksmes.*',
                    'Lietotajs.lietotaja_vards',
                    'Lietotajs.epasts',
                    'Lietotajs.foto',
                    'parent_user.lietotaja_vards as vecakais_lietotaja_vards'
                )
                ->orderBy('Atsauksmes.created_at', 'asc')
                ->get();

                $items = [];
                $roots = [];

                foreach ($allReviews as $review) {
                    $item = [
                        'Atsauksmes_ID' => $review->Atsauksmes_ID,
                        'Lietotaja_ID' => $review->Lietotaja_ID,
                        'Gramatas_ID' => $review->Gramatas_ID,
                        'vertejums' => $review->vertejums,
                        'komentārs' => $review->komentārs,
                        'vecakais_komentars' => $review->vecakais_komentars,
                        'vecakais_lietotaja_vards' => $review->vecakais_lietotaja_vards,
                        'created_at' => $review->created_at,
                        'updated_at' => $review->updated_at,
                        'lietotaja_vards' => $review->lietotaja_vards,
                        'epasts' => $review->epasts,
                        'foto' => $review->foto,
                        'atbildes' => []
                  ];
                
                  $items[$review->Atsauksmes_ID] = $item;
                }

                foreach ($items as $id => $item) {
                    $parentId = $item['vecakais_komentars'];
                    
                    if ($parentId === null) {
                        $roots[] = $id;
                    } else if (isset($items[$parentId])) {
                        $items[$parentId]['atbildes'][] = $id;
                    } else {
                        
                        $roots[] = $id;
                    }
                }

                $result = [];
                foreach ($roots as $rootId) {
                    $result[] = $this->buildTree($items, $rootId);
                }
                
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {

            Log::error('ERROR in bookReviews');
            Log::error('Message: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot atsauksmes'
            ], 500);
        }
    }

    private function buildTree(&$items, $id)
    {
        $item = $items[$id];
        $children = [];
        
        foreach ($item['atbildes'] as $childId) {
            $children[] = $this->buildTree($items, $childId);
        }
        
        $item['atbildes'] = $children;
        return $item;
    }

    /**
     * Parbaudišana uz komentaru piejamību no lietotaja
     * Kas dara: Pārbauda, vai lietotājs ir atstājis atsauksmi par konkrētu grāmatu
     * Kad izmantojas: Lai slept/paradit pogu 'rakstit komentaru'
     */
    public function check($bookId)
    {
        try {
            $user = $this->getUserFromToken(request());
            
            if (!$user) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Lietotājs nav autentificēts'
                ], 401);
            }
            
            $review = DB::table('Atsauksmes')
                ->where('Lietotaja_ID', $user->kodsID)
                ->where('Gramatas_ID', $bookId)
                ->whereNull('vecakais_komentars')
                ->first();
                
            if ($review) {
                return response()->json([
                    'exists' => true,
                    'review' => $review
                ]);
            } else {
                return response()->json([
                    'exists' => false
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'exists' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pievienošana vai atjaunināšana atsauksmei
     * Kas dara: Ja lietotājs jau ir atstājis atsauksmi, tad to atjaunina, citādi pievieno jaunu
     * Kad izmantojas: Kad lietotājs iesniedz atsauksmi par grāmatu
    */
    public function store(Request $request)
    {
        Log::info('REVIEW STORE CALLED');
        Log::info('Request data:', $request->all());
        
        try {
            
            $user = $this->getUserFromToken($request);
            
            Log::info('User from token:', ['user' => $user ? 'found' : 'not found']);
            
            if (!$user) {
                Log::error('User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'Lietotājs nav autentificēts'
                ], 401);
            }
            
            Log::info('User ID: ' . $user->kodsID);
            
            $validator = Validator::make($request->all(), [
                'gramatas_id' => 'required',
                'vertejums' => 'nullable|integer|min:1|max:5',
                'komentars' => 'nullable|string|max:500',
                'vecakais_komentars' => 'nullable|integer|exists:Atsauksmes,Atsauksmes_ID'
            ]);
            
            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            
            $bookExists = DB::table('Gramata')
                ->where('ISBN', $request->gramatas_id)
                ->exists();
                
            Log::info('Book exists: ' . ($bookExists ? 'yes' : 'no'));
            
            if (!$bookExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grāmata nav atrasta'
                ], 404);
            }

            if ($request->has('vecakais_komentars') && $request->vecakais_komentars) {
                
                
                $parentExists = DB::table('Atsauksmes')
                    ->where('Atsauksmes_ID', $request->vecakais_komentars)
                    ->where('Gramatas_ID', $request->gramatas_id)
                    ->exists();
                    
                if (!$parentExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vecākais komentārs nav atrasts šajā grāmatā'
                    ], 404);
                }

                DB::table('Atsauksmes')->insert([
                    'Lietotaja_ID' => $user->kodsID,
                    'Gramatas_ID' => $request->gramatas_id,
                    'vertejums' => null,
                    'komentārs' => $request->komentars,
                    'vecakais_komentars' => $request->vecakais_komentars,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                 Log::info('Reply created');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Atbilde pievienota!'
                ]);
            }
            
            
            $existing = DB::table('Atsauksmes')
                ->where('Lietotaja_ID', $user->kodsID)
                ->where('Gramatas_ID', $request->gramatas_id)
                ->whereNull('vecakais_komentars')
                ->first();
                
            Log::info('Existing review: ' . ($existing ? 'yes' : 'no'));
                
            if ($existing) {

                $updateData = [
                    'komentārs' => $request->komentars,
                    'updated_at' => now()
                ];
                
                if ($request->has('vertejums') && $request->vertejums) {
                    $updateData['vertejums'] = $request->vertejums;
                }
                
                DB::table('Atsauksmes')
                    ->where('Atsauksmes_ID', $existing->Atsauksmes_ID)
                    ->update($updateData);
                
                Log::info('Review updated');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Atsauksme atjaunināta!'
                ]);
            } else {
                
                DB::table('Atsauksmes')->insert([
                    'Lietotaja_ID' => $user->kodsID,
                    'Gramatas_ID' => $request->gramatas_id,
                    'vertejums' => $request->vertejums,
                    'komentārs' => $request->komentars,
                    'vecakais_komentars' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                Log::info('New review created');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Atsauksme pievienota!'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Exception: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());
            
            return response()->json([
                'success' => false,
                'message' => 'Kļūda saglabājot atsauksmi: ' . $e->getMessage()
            ], 500);
        }
    }
}