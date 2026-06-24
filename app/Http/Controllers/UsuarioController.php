<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Configuracion;
use App\Models\Zona;

class UsuarioController extends Controller
{
    // Mostrar formulario de login
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    // Procesar login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $credenciales = $request->only('email', 'password');

        if (Auth::attempt($credenciales)) {
            // Verificar que el usuario esté activo
            if (Auth::user()->estado !== 'activo') {
                Auth::logout();
                return back()->withErrors(['email' => 'Tu cuenta está inactiva.']);
            }
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Credenciales incorrectas.']);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // Listar usuarios (Admin)
    public function index()
    {
        $usuarios = User::orderBy('created_at', 'desc')->get();
        return view('usuarios.index', compact('usuarios'));
    }

    // Formulario crear usuario
    public function crear()
    {
        return view('usuarios.crear');
    }

    // Guardar nuevo usuario
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'rol'      => 'required|in:operario,supervisor,administrador'
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'rol'      => $request->rol,
            'estado'   => 'activo'
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    // Cambiar estado usuario (activo/inactivo)
    public function cambiarEstado(Request $request, $id)
    {
        $usuario = User::findOrFail($id);
        $usuario->estado = $usuario->estado === 'activo' ? 'inactivo' : 'activo';
        $usuario->save();

        return redirect()->route('usuarios.index')->with('success', 'Estado actualizado.');
    }

    // Ver configuracion
    public function configuracion()
    {
        $config = Configuracion::first();
        $zonas  = Zona::where('estado', '!=', 'eliminada')->get();
        return view('configuracion.index', compact('config', 'zonas'));
    }

    // Guardar configuracion
    public function guardarConfiguracion(Request $request)
    {
        $request->validate([
            'tiempo_maximo_dias'   => 'required|integer|min:1',
            'peso_maximo_pequeno'  => 'required|numeric|min:0.1',
            'peso_maximo_mediano'  => 'required|numeric|min:0.1',
            'id_zona_reubicacion'  => 'nullable|exists:zonas,id'
        ]);

        Configuracion::updateOrCreate(
            ['id' => 1],
            [
                'tiempo_maximo_dias'  => $request->tiempo_maximo_dias,
                'peso_maximo_pequeno' => $request->peso_maximo_pequeno,
                'peso_maximo_mediano' => $request->peso_maximo_mediano,
                'id_zona_reubicacion' => $request->id_zona_reubicacion,
                'fecha_actualizacion' => now(),
                'id_admin'            => Auth::id()
            ]
        );

        return redirect()->route('configuracion')->with('success', 'Configuración guardada.');
    }
}