<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'nunllable|string|max:255',
            'phone' => ['required', 'string', 'unique:users', new PhoneNumber],
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
    
        $token = $user->createToken('auth_token')->plainTextToken;

          return response()->json([
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
    ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', new PhoneNumber],
            'password' => 'required|string',
        ]);
    
        if (!Auth::attempt($request->only('phone', 'password'))) {
            return response()->json([
                'error' => 'The provided credentials are incorrect.',
            ], 401); // 401 Unauthorized
        }
    
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json(['access_token' => $token, 'token_type' => 'Bearer','user_id' => $user->id ]);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
