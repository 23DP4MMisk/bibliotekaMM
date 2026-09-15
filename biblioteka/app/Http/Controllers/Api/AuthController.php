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
        Log::info('REGISTRĀCIJA');

        
       
        $validator = Validator::make($request->all(), [
            'epasts' => 'required|email|max:100|unique:Lietotajs',
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
            
            Log::info(' Lietotājs izveidots:', ['id' => $lietotajs->kodsID]);
            
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
            Log::error(' Kļūda:', ['message' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Servera kļūda: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'epasts' => 'required|email'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'exists' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = Lietotajs::where('epasts', $request->epasts)->first();
        
        return response()->json([
            'exists' => $user !== null
        ]);
    }
    
    public function login(Request $request)
    {
        
        
        Log::info(' Ieja ');
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
    
            Log::info(' Ieja veiksmiga. ID: ' . $user->kodsID);
            
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
                        'status' => $user->status,
                        'foto' => $user->foto
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
        Log::info('LOGOUT ');
    
   
        return response()->json([
         'success' => true,
         'message' => 'Izrakstīšanās veiksmīga'
        ]);
    }
    
    
    
       
      

    public function testCreateUser(Request $request)
    {
        Log::info('TEST CREATE USER ');
        
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

    public function getProfile(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lietotājs nav autentificēts'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'kodsID' => $user->kodsID,
                    'lietotaja_vards' => $user->lietotaja_vards,
                    'epasts' => $user->epasts,
                    'loma' => $user->loma,
                    'status' => $user->status,
                    'foto' => $user->foto,
                    'bio' => $user->bio,
                    'pilseta' => $user->pilseta,
                    'dzim_datums' => $user->dzim_datums,
                    'registresanas_datums' => $user->registresanas_datums,
                    'created_at' => $user->created_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kļūda ielādējot profilu'
            ], 500);
        }
    }

     public function updateProfile(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lietotājs nav autentificēts'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'lietotaja_vards' => 'nullable|string|max:50',
                'bio' => 'nullable|string|max:500',
                'pilseta' => 'nullable|string|max:100',
                'dzim_datums' => 'nullable|date|before:today'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];

            if ($request->has('lietotaja_vards')) {
                $updateData['lietotaja_vards'] = $request->lietotaja_vards;
            }
            if ($request->has('bio')) {
                $updateData['bio'] = $request->bio;
            }
            if ($request->has('pilseta')) {
                $updateData['pilseta'] = $request->pilseta;
            }
            if ($request->has('dzim_datums')) {
                $updateData['dzim_datums'] = $request->dzim_datums;
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profils veiksmīgi atjaunināts!',
                'data' => [
                    'kodsID' => $user->kodsID,
                    'lietotaja_vards' => $user->lietotaja_vards,
                    'epasts' => $user->epasts,
                    'foto' => $user->foto,
                    'bio' => $user->bio,
                    'pilseta' => $user->pilseta,
                    'dzim_datums' => $user->dzim_datums
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kļūda atjauninot profilu'
            ], 500);
        }
    }
    
     public function uploadAvatar(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lietotājs nav autentificēts'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $file = $request->file('foto');
            $filename = 'avatar_' . $user->kodsID . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');

            $user->update(['foto' => '/storage/' . $path]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar veiksmīgi augšupielādēts!',
                'data' => [
                    'foto' => '/storage/' . $path
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error uploading avatar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kļūda augšupielādējot avataru'
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lietotājs nav autentificēts'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!Hash::check($request->current_password, $user->parole)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pašreizējā parole nav pareiza'
                ], 400);
            }

            $user->update([
                'parole' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Parole veiksmīgi mainīta!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error changing password: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kļūda mainot paroli'
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $this->getUserFromToken($request);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lietotājs nav autentificēts'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!Hash::check($request->password, $user->parole)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nepareiza parole'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Profils veiksmīgi dzēsts'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Kļūda dzēšot profilu'
            ], 500);
        }
    }

    private function getUserFromToken($request)
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        
        $token = str_replace('Bearer ', '', $authHeader);
        $parts = explode('_', $token);
        $userId = $parts[0] ?? null;
        
        if (!$userId) {
            return null;
        }
        
        return Lietotajs::where('kodsID', $userId)->first();
    }





}