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

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('=== SIMPLE REGISTER ===');
        Log::info('Data:', $request->all());
        
       
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
                'loma' =>  $request->loma === 'admin' ? 'admin' : 'registretajsklients',
                'registresanas_datums' => Carbon::now(),
                'status' => 'aktivs'
            ]);
            
            Log::info('✅ Lietotājs izveidots:', ['id' => $lietotajs->kodsID]);
            
            
            
            return response()->json([
                'success' => true,
                'message' => 'Reģistrācija veiksmīga',
                'lietotajs' => [
                    'kodsID' => $lietotajs->kodsID,
                    'lietotaja_vards' => $lietotajs->lietotaja_vards,
                    'epasts' => $lietotajs->epasts,
                    'loma' => $lietotajs->loma,
                    'status' => $lietotajs->status
                ]
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Kļūda: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function login(Request $request)
    {
        Log::info('=== SIMPLE LOGIN ===');
        
      
        $lietotajs = Lietotajs::where('epasts', $request->epasts)->first();
        
        if (!$lietotajs) {
            return response()->json([
                'success' => false,
                'message' => 'Lietotājs nav atrasts'
            ], 404);
        }
        
       
        if (!Hash::check($request->parole, $lietotajs->parole)) {
            return response()->json([
                'success' => false,
                'message' => 'Nepareiza parole'
            ], 401);
        }
        
        
        if ($lietotajs->status !== 'aktivs') {
            return response()->json([
                'success' => false,
                'message' => 'Konts bloķēts'
            ], 403);
        }
        
        
        Auth::login($lietotajs);

        
        $request->session()->regenerate();

        Log::info('Pieslēgšanās veiksmīga:', [
            'user_id' => $lietotajs->kodsID,
            'session_id' => session()->getId()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Pieslēgšanās veiksmīga',
            'lietotajs' => [
                'kodsID' => $lietotajs->kodsID,
                'lietotaja_vards' => $lietotajs->lietotaja_vards,
                'epasts' => $lietotajs->epasts,
                'loma' => $lietotajs->loma,
                'status' => $lietotajs->status
            ],
            'session_id' => session()->getId()
        ]);
    }
    
    public function logout(Request $request)
    {
        Auth::logout();

       
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Izrakstīšanās veiksmīga');
        
        return response()->json([
            'success' => true,
            'message' => 'Izrakstīšanās veiksmīga'
        ]);
    }
    
    public function checkAuth(Request $request)
    {
        Log::info('=== AUTENTIFIKĀCIJAS PĀRBAUDE ===');
        Log::info('Session ID:', ['id' => session()->getId()]);
        Log::info('Auth check:', ['check' => Auth::check()]);

        if (!Auth::check()) {
        Log::info('❌ User NOT authenticated');
        return response()->json([
            'success' => true,
            'authenticated' => false,
            'message' => 'Nav pieslēgts'
        ]);
        }

       
        $user = Auth::user();

        Log::info('✅ User authenticated:', [
        'id' => $user->kodsID,
        'name' => $user->lietotaja_vards,
        'email' => $user->epasts
        ]); 

            return response()->json([
                'success' => true,
                'authenticated' => true,
                'lietotajs' => [
                    'kodsID' => $user->kodsID,
                    'lietotaja_vards' => $user->lietotaja_vards,
                    'epasts' => $user->epasts,
                    'loma' => $user->loma,
                    'status' => $user->status
                ],
                 'session_id' => session()->getId()
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