<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lietotajs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('=== REGISTRĀCIJA ===');

        
       
        $validator = Validator::make($request->all(), [
            'epasts' => 'required|email|max:100|unique:lietotajs',
            'lietotaja_vards' => 'required|string|max:50',
            'parole' => 'required|string|min:3'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $lietotajs = Lietotajs::create([
                'lietotaja_vards' => substr($request->lietotaja_vards, 0, 10),
                'epasts' => substr($request->epasts, 0, 20),
                'parole' => Hash::make($request->parole),
                'loma' =>  $request->loma === 'admins' ? 'admins' : 'registretajsklients',
                'registresanas_datums' => Carbon::now(),
                'status' => 'aktivs'
            ]);
            
         $token = $lietotajs->kodsID . '_' . time();
            
            Log::info('✅ Lietotājs izveidots:', ['id' => $lietotajs->kodsID]);
            
            return response()->json([
                'success' => true,
                'message' => 'Reģistrācija veiksmīga!',
                'token' => $token,
                'lietotajs' => [
                    'kodsID' => $lietotajs->kodsID,
                    'lietotaja_vards' => $lietotajs->lietotaja_vards,
                    'epasts' => $lietotajs->epasts,
                    'loma' => $lietotajs->loma,
                    'status' => $lietotajs->status
                ]
            ], 201);

            } catch (\Exception $e) {
            Log::error('❌ Kļūda:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Servera kļūda: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function login(Request $request)
    {
        
        
        Log::info('=== Ieja ===');
        Log::info('Email: ' . $request->epasts);
        
        
        $validator = Validator::make($request->all(), [
            'epasts' => 'required|email',
            'parole' => 'required|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Lietotajs::where('epasts', $request->epasts)->first();
        
        if (!$user) {
            Log::error('Lietotājs nav atrasts: ' . $request->epasts);
            return response()->json([
                'success' => false,
                'message' => 'Nepareizs e-pasts vai parole'
            ], 401);
        }

        if (!Hash::check($request->parole, $user->parole)) {
            Log::error('Nepareiza parole lietotājam: ' . $request->epasts);
            return response()->json([
                'success' => false,
                'message' => 'Nepareizs e-pasts vai parole'
            ], 401);
        }

         
            $token = $user->kodsID . '_' . time();
    
            Log::info('✅ Ieja veiksmiga. ID: ' . $user->kodsID);
            
            return response()->json([
                'success' => true,
                'message' => 'Ieja veiksmīga!',
                'token' => $token,
                'lietotajs' => [
                    'kodsID' => $user->kodsID,
                    'lietotaja_vards' => $user->lietotaja_vards,
                    'epasts' => $user->epasts,
                    'loma' => $user->loma,
                    'status' => $user->status
                ]
            ]);

           
        
        
    }

    public function checkAuth(Request $request)
    {
        try {
        
        $token = $request->header('Authorization');
        
        if (!$token) {
            return response()->json([
                'authenticated' => false,
                'message' => 'No token provided'
            ]);
        }
        
        
        $token = str_replace('Bearer ', '', $token);
        
        $parts = explode('_', $token);
        
        if (count($parts) === 2) {
            $userId = $parts[0];
            $user = Lietotajs::find($userId);
        if ($user) {
                return response()->json([
                    'authenticated' => true,
                    'lietotajs' => [
                        'kodsID' => $user->kodsID,
                        'lietotaja_vards' => $user->lietotaja_vards,
                        'epasts' => $user->epasts,
                        'loma' => $user->loma,
                        'status' => $user->status
                    ]
                ]);
            }
        }

          return response()->json([
            'authenticated' => false,
            'message' => 'Invalid token'
        ]);
        
    } catch (\Exception $e) {
        
        return response()->json([
            'authenticated' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
 }
    
   
    
    public function logout(Request $request)
    {
        Log::info('=== LOGOUT ===');
    
   
        return response()->json([
         'success' => true,
         'message' => 'Izrakstīšanās veiksmīga'
        ]);
    }
    
    
    
       
      

    public function testCreateUser(Request $request)
    {
        Log::info('=== TEST CREATE USER ===');
        
        try {
            $lietotajs = Lietotajs::create([
                'lietotaja_vards' => 'test_' . rand(100, 999),
                'epasts' => 'test_' . rand(1000, 9999) . '@test.com',
                'parole' => Hash::make('password123'),
                'loma' => 'registretajsklients',
                'registresanas_datums' => Carbon::now(),
                'status' => 'aktivs'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Testa lietotājs izveidots',
                'user_id' => $lietotajs->kodsID
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }
}