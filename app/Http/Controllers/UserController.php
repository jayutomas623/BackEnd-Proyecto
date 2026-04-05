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
            'name'                        => 'required|string|max:255',
            'email'                       => 'required|string|email|unique:users',
            'password'                    => 'required|string|min:6',
            'role_id'                     => 'required|exists:roles,id',
            'ci'                          => 'required|string|max:20',
            'telefono'                    => 'nullable|string|max:20',
            'direccion'                   => 'nullable|string|max:500',
            'fecha_nacimiento'            => 'nullable|date',
            'fecha_contratacion'          => 'nullable|date',
            'contacto_emergencia_nombre'  => 'nullable|string|max:255',
            'contacto_emergencia_telefono'=> 'nullable|string|max:20',
        ]);
    
        $usuario = User::create([
            'name'                        => $request->name,
            'email'                       => $request->email,
            'password'                    => Hash::make($request->password),
            'role_id'                     => $request->role_id,
            'ci'                          => $request->ci,
            'telefono'                    => $request->telefono,
            'direccion'                   => $request->direccion,
            'fecha_nacimiento'            => $request->fecha_nacimiento,
            'fecha_contratacion'          => $request->fecha_contratacion,
            'contacto_emergencia_nombre'  => $request->contacto_emergencia_nombre,
            'contacto_emergencia_telefono'=> $request->contacto_emergencia_telefono,
            'estado'                      => true,
        ]);
    
        return response()->json(['mensaje' => 'Empleado registrado', 'usuario' => $usuario], 201);
    }
    
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
    
        $request->validate([
            'name'                        => 'required|string|max:255',
            'email'                       => 'required|string|email|unique:users,email,'.$id,
            'role_id'                     => 'required|exists:roles,id',
            'ci'                          => 'required|string|max:20',
            'telefono'                    => 'nullable|string|max:20',
            'direccion'                   => 'nullable|string|max:500',
            'fecha_nacimiento'            => 'nullable|date',
            'fecha_contratacion'          => 'nullable|date',
            'contacto_emergencia_nombre'  => 'nullable|string|max:255',
            'contacto_emergencia_telefono'=> 'nullable|string|max:20',
            'estado'                      => 'boolean',
        ]);
    
        $datos = $request->except('password');
        if ($request->filled('password')) {
            $datos['password'] = Hash::make($request->password);
        }
    
        $usuario->update($datos);
    
        return response()->json(['mensaje' => 'Empleado actualizado', 'usuario' => $usuario]);
    }

    public function perfil(Request $request)
    {
        return response()->json($request->user()->load('role'));
    }

    public function actualizarPerfil(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'telefono'         => 'nullable|string|max:20',
            'password_actual'  => 'nullable|string',
            'password_nuevo'   => 'nullable|string|min:6|required_with:password_actual',
        ]);
        if ($request->filled('password_actual')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->password_actual, $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta.'
                ], 422);
            }
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password_nuevo);
        }

        $user->name     = $request->name;
        $user->telefono = $request->telefono;
        $user->save();

        return response()->json([
            'mensaje' => 'Perfil actualizado correctamente.',
            'usuario' => $user->load('role'),
        ]);
    }
}