<?php

namespace App\Http\Controllers\Responsable;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\ConfiguracionUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    /**
     * Constructor - Solo RESPONSABLE puede gestionar usuarios
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:RESPONSABLE']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $usuarios = Usuario::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nombre', 'ILIKE', "%{$search}%")
                    ->orWhere('apellido', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('registro', 'ILIKE', "%{$search}%");
            })
            ->when($request->rol, function ($query, $rol) {
                $query->where('rol', $rol);
            })
            ->when($request->activo !== null, function ($query) use ($request) {
                $query->where('activo', $request->boolean('activo'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'filters' => $request->only(['search', 'rol', 'activo']),
            'roles' => ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'],
            'estadisticas' => [
                'total' => Usuario::count(),
                'activos' => Usuario::activo()->count(),
                'responsables' => Usuario::role('RESPONSABLE')->count(),
                'administrativos' => Usuario::role('ADMINISTRATIVO')->count(),
                'tutores' => Usuario::role('TUTOR')->count(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Usuarios/Create', [
            'roles' => [
                'RESPONSABLE' => 'Responsable del Sistema',
                'ADMINISTRATIVO' => 'Personal Administrativo',
                'TUTOR' => 'Tutor de Cursos'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'carnet' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'unique:USUARIO,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol' => ['required', Rule::in(['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'])],
            'registro' => ['required', 'string', 'max:20', 'unique:USUARIO,registro'],
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'carnet.required' => 'El carnet es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.unique' => 'Ya existe un usuario con este email.',
            'rol.required' => 'El rol es obligatorio.',
            'rol.in' => 'El rol seleccionado no es válido.',
            'registro.required' => 'El registro es obligatorio.',
            'registro.unique' => 'Ya existe un usuario con este registro.',
        ]);

        // Generar contraseña automática basada en datos institucionales
        $passwordDefecto = 'CICIT' . $validated['carnet'] . '2025';

        $usuario = Usuario::create([
            'nombre' => $validated['nombre'],
            'apellido' => $validated['apellido'],
            'carnet' => $validated['carnet'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'],
            'rol' => $validated['rol'],
            'registro' => $validated['registro'],
            'password' => Hash::make($passwordDefecto),
            'activo' => true,
        ]);

        // Crear configuración por defecto para el usuario
        ConfiguracionUsuario::crearConfiguracionDefecto($usuario->id);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario creado exitosamente. Contraseña temporal: {$passwordDefecto}");
    }

    /**
     * Display the specified resource.
     */
    public function show(Usuario $usuario): Response
    {
        $usuario->load(['configuracion.tema']);

        // Estadísticas específicas según el rol
        $estadisticas = [];

        if ($usuario->isTutor()) {
            $usuario->load(['cursos.inscripciones']);
            $estadisticas = [
                'cursos_asignados' => $usuario->cursos->count(),
                'cursos_activos' => $usuario->cursos->where('activo', true)->count(),
                'total_estudiantes' => $usuario->cursos->sum(function ($curso) {
                    return $curso->inscripciones->count();
                }),
            ];
        }

        return Inertia::render('Usuarios/Show', [
            'usuario' => $usuario,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Usuario $usuario): Response
    {
        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario,
            'roles' => [
                'RESPONSABLE' => 'Responsable del Sistema',
                'ADMINISTRATIVO' => 'Personal Administrativo',
                'TUTOR' => 'Tutor de Cursos'
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usuario $usuario)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'carnet' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', Rule::unique('USUARIO', 'email')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol' => ['required', Rule::in(['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'])],
            'registro' => ['required', 'string', 'max:20', Rule::unique('USUARIO', 'registro')->ignore($usuario->id)],
            'activo' => ['boolean'],
        ], [
            'email.unique' => 'Ya existe otro usuario con este email.',
            'registro.unique' => 'Ya existe otro usuario con este registro.',
            'rol.in' => 'El rol seleccionado no es válido.',
        ]);

        $usuario->update($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Desactivar usuario (soft delete)
     * No eliminamos registros, solo cambiamos estado
     */
    public function desactivar(Usuario $usuario)
    {
        // No permitir desactivar el último responsable
        if ($usuario->isResponsable() && $usuario->activo && Usuario::role('RESPONSABLE')->activo()->count() <= 1) {
            return back()->with('error', 'No se puede desactivar el último responsable del sistema.');
        }

        $usuario->update(['activo' => false]);

        return back()->with('success', 'Usuario desactivado exitosamente.');
    }

    /**
     * Reactivar usuario
     */
    public function reactivar(Usuario $usuario)
    {
        $usuario->update(['activo' => true]);

        return back()->with('success', 'Usuario reactivado exitosamente.');
    }

    /**
     * Cambiar estado activo/inactivo del usuario
     */
    public function toggleActivo(Usuario $usuario)
    {
        // No permitir desactivar el último responsable
        if ($usuario->isResponsable() && $usuario->activo && Usuario::role('RESPONSABLE')->activo()->count() <= 1) {
            return back()->with('error', 'No se puede desactivar el último responsable del sistema.');
        }

        $usuario->update(['activo' => !$usuario->activo]);

        $estado = $usuario->activo ? 'activado' : 'desactivado';

        return back()->with('success', "Usuario {$estado} exitosamente.");
    }

    /**
     * Resetear contraseña del usuario
     */
    public function resetPassword(Request $request, Usuario $usuario)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Contraseña actualizada exitosamente.');
    }

    /**
     * Obtener lista de tutores para selects
     */
    public function getTutores()
    {
        $tutores = Usuario::role('TUTOR')
            ->activo()
            ->select('id', 'nombre', 'apellido')
            ->get()
            ->map(function ($tutor) {
                return [
                    'id' => $tutor->id,
                    'nombre_completo' => $tutor->nombre_completo,
                ];
            });

        return response()->json($tutores);
    }

    /**
     * Obtener lista de responsables para selects
     */
    public function getResponsables()
    {
        $responsables = Usuario::role('RESPONSABLE')
            ->activo()
            ->select('id', 'nombre', 'apellido')
            ->get()
            ->map(function ($responsable) {
                return [
                    'id' => $responsable->id,
                    'nombre_completo' => $responsable->nombre_completo,
                ];
            });

        return response()->json($responsables);
    }

    /**
     * Obtener lista de administrativos para selects
     */
    public function getAdministrativos()
    {
        $administrativos = Usuario::role('ADMINISTRATIVO')
            ->activo()
            ->select('id', 'nombre', 'apellido')
            ->get()
            ->map(function ($administrativo) {
                return [
                    'id' => $administrativo->id,
                    'nombre_completo' => $administrativo->nombre_completo,
                ];
            });

        return response()->json($administrativos);
    }

    /**
     * Obtener todos los usuarios por rol (método genérico)
     */
    public function getUsuariosPorRol(Request $request)
    {
        $rol = $request->input('rol');

        if (!in_array($rol, ['RESPONSABLE', 'ADMINISTRATIVO', 'TUTOR'])) {
            return response()->json(['error' => 'Rol no válido'], 400);
        }

        $usuarios = Usuario::role($rol)
            ->activo()
            ->select('id', 'nombre', 'apellido', 'email', 'registro')
            ->get()
            ->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'nombre_completo' => $usuario->nombre_completo,
                    'email' => $usuario->email,
                    'registro' => $usuario->registro,
                ];
            });

        return response()->json($usuarios);
    }

    /**
     * Estadísticas generales de usuarios para dashboard
     */
    public function estadisticas()
    {
        $stats = [
            'total_usuarios' => Usuario::count(),
            'usuarios_activos' => Usuario::activo()->count(),
            'nuevos_este_mes' => Usuario::whereMonth('created_at', now()->month)->count(),
            'por_rol' => [
                'RESPONSABLE' => Usuario::role('RESPONSABLE')->activo()->count(),
                'ADMINISTRATIVO' => Usuario::role('ADMINISTRATIVO')->activo()->count(),
                'TUTOR' => Usuario::role('TUTOR')->activo()->count(),
            ],
            'actividad_reciente' => Usuario::latest('updated_at')->take(5)->get(['id', 'nombre', 'apellido', 'rol', 'updated_at']),
        ];

        return response()->json($stats);
    }
}
