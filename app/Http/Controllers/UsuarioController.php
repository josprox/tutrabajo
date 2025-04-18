<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

/**
 * Class UsuarioController
 * @package App\Http\Controllers
 */
class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuarios = Usuario::paginate(10);

        return view('usuario.index', compact('usuarios'))
            ->with('i', (request()->input('page', 1) - 1) * $usuarios->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $usuario = new Usuario();
        return view('usuario.create', compact('usuario'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Usuario::$rules);

        $usuario = Usuario::create($request->all());

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $usuario = Usuario::find($id);

        return view('usuario.show', compact('usuario'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $usuario = Usuario::find($id);

        return view('usuario.edit', compact('usuario'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Usuario $usuario
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Usuario $usuario)
    {
        request()->validate(Usuario::$rules);

        $usuario->update($request->all());

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario updated successfully');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $usuario = Usuario::find($id)->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario deleted successfully');
    }

    /**
     * Mostrar la información de un usuario usando el token_user.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function DatosUsuario($token)
    {
        // Buscar al usuario por el token_user
        $usuario = Usuario::where('token_user', $token)->first();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        return response()->json($usuario);
    }

    /**
     * Actualizar las especialidades y curriculum del usuario por ID y token_user.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function actualizarDetalles($token, Request $request)
    {
        // Buscar al usuario por token_user
        $usuario = Usuario::where('token_user', $token)->first();

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Validar los campos que se pueden actualizar (especialidades y curriculum)
        $request->validate([
            'especialidades' => 'nullable|string',
            'curriculum' => 'nullable|string',
        ]);

        // Actualizar solo los campos permitidos
        if ($request->has('especialidades')) {
            $usuario->especialidades = $request->input('especialidades');
        }

        if ($request->has('curriculum')) {
            $usuario->curriculum = $request->input('curriculum');
        }

        // Guardar los cambios
        $usuario->save();

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'usuario' => $usuario
        ]);
    }
    public function crearUsuario(Request $request)
    {
        // Validar los datos que se recibirán para la creación del usuario
        $validated = $request->validate([
            'token_user' => 'required|string|uuid', // Aceptar UUID en el campo token_user
            'especialidades' => 'required|string',
            'curriculum' => 'required|string',
        ]);

        // Verificar si el usuario con el mismo token_user ya existe
        $usuarioExistente = Usuario::where('token_user', $validated['token_user'])->first();

        if ($usuarioExistente) {
            // Si el usuario ya existe, devolver un mensaje de error
            return response()->json(['message' => 'El usuario con este token ya existe'], 409); // 409 es el código de respuesta para conflicto
        }

        // Crear el usuario con los datos validados
        $usuario = Usuario::create([
            'token_user' => $validated['token_user'], // Usar el UUID proporcionado por el cliente
            'especialidades' => $validated['especialidades'],
            'curriculum' => $validated['curriculum'],
        ]);

        // Devolver una respuesta JSON indicando que el usuario se ha creado correctamente
        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201); // 201 es el código de respuesta para 'Creado'
    }


    public function eliminarUsuarioPorToken($token_user)
    {
        // Buscar al usuario por el token_user
        $usuario = Usuario::where('token_user', $token_user)->first();

        // Si el usuario no existe, devolver un mensaje de error
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Eliminar el usuario
        $usuario->delete();

        // Devolver una respuesta JSON indicando que el usuario ha sido eliminado
        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }
}
