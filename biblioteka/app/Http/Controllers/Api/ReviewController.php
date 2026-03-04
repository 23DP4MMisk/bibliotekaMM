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

    public function bookReviews($isbn)
    {
        try {
            $reviews = DB::table('Atsauksmes')
                ->join('Lietotajs', 'Atsauksmes.Lietotaja_ID', '=', 'Lietotajs.kodsID')
                ->where('Atsauksmes.Gramatas_ID', $isbn)
                ->select(
                    'Atsauksmes.*',
                    'Lietotajs.lietotaja_vards',
                    'Lietotajs.epasts'
                )
                ->orderBy('Atsauksmes.created_at', 'desc')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot atsauksmes'
            ], 500);
        }
    }

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

    public function store(Request $request)
    {
        Log::info('=== REVIEW STORE CALLED ===');
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
                'vertejums' => 'required|integer|min:1|max:5',
                'komentars' => 'nullable|string|max:500'
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
            
            
            $existing = DB::table('Atsauksmes')
                ->where('Lietotaja_ID', $user->kodsID)
                ->where('Gramatas_ID', $request->gramatas_id)
                ->first();
                
            Log::info('Existing review: ' . ($existing ? 'yes' : 'no'));
                
            if ($existing) {
                
                DB::table('Atsauksmes')
                    ->where('Atsauksmes_ID', $existing->Atsauksmes_ID)
                    ->update([
                        'vertejums' => $request->vertejums,
                        'komentārs' => $request->komentars,
                        'updated_at' => now()
                    ]);
                
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