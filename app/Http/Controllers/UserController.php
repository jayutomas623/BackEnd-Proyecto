<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('role')->get();
        return response()->json($usuarios);
    }

    public function getRoles()
    {
        return response()->json(Role::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'ci' => 'nullable|string',
            'telefono' => 'nullable|string',
        ]);

        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
            'ci' => $request->ci,
            'telefono' => $request->telefono,
            'estado' => true
        ]);

        return response()->json(['mensaje' => 'Usuario creado con éxito', 'usuario' => $usuario], 201);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,'.$id,
            'role_id' => 'required|exists:roles,id',
            'estado' => 'boolean'
        ]);

        $datosActualizar = $request->except('password');

        if ($request->filled('password')) {
            $datosActualizar['password'] = Hash::make($request->password);
        }

        $usuario->update($datosActualizar);

        return response()->json(['mensaje' => 'Usuario actualizado', 'usuario' => $usuario]);
    }
}