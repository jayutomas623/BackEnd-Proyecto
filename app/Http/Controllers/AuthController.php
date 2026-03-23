<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

      
        if (Auth::attempt($request->only('email', 'password'))) {
            
           
            $user = User::with('role')->find(Auth::id());

            if (isset($user->estado) && $user->estado == 0) {
                return response()->json([
                    'mensaje' => 'Cuenta desactivada. Contacte al administrador.'
                ], 403);
            }
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'mensaje' => 'Inicio de sesión exitoso',
                'token' => $token,
                'usuario' => $user
            ], 200);
        }

        return response()->json([
            'mensaje' => 'Credenciales incorrectas'
        ], 401);
    }
}