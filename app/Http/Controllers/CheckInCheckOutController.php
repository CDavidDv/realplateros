<?php

namespace App\Http\Controllers;

use App\Models\CheckInCheckOut;
use App\Models\Usuario;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CheckInCheckOutController extends Controller
{
    // Listar todos los check-in/check-out de una sucursal
 
    public function index(Sucursal $sucursal)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener todos los check-ins y la información de los trabajadores
        $checkIns = CheckInCheckOut::with('user')
            ->where('sucursal_id', $sucursalId)
            ->whereDate('created_at', today())
            ->get();

        return Inertia::render('Checador/index', [
            'checkIns' => $checkIns, 
            'sucursal' => $sucursal
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        $query = CheckInCheckOut::with('user')
            ->whereHas('user', function ($query) use ($sucursalId) {
                $query->where('sucursal_id', $sucursalId);
            })
            ->whereBetween('created_at', [$request->startDate, $request->endDate]);

        $checkIns = $query->get();

        return Inertia::render('Checador/index', [
            'checkIns' => $checkIns, 
        ]);
    }

    public function searchCheckInsOuts(Request $request)
    {
        
        // Validar los datos de entrada
        $request->validate([
            'email' => 'required|string',
            'searchDate' => 'required|date',
        ]);

        // Buscar el usuario por email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Usuario no encontrado',
            ], 404);
        }

        // Buscar los registros de CheckInCheckOut para el usuario y la fecha especificada
        $userSearchResults = CheckInCheckOut::with(['user', 'sucursal'])
            ->where('user_id', $user->id)
            ->whereDate('created_at', $request->searchDate)
            ->get();

        // Devolver los resultados en formato JSON
        return response()->json([
            'checkIns' => $userSearchResults,
        ]);
    }

public function checkInOut(Request $request) {
    // Validate the request
    $request->validate([
        'email' => 'required',
        'password' => 'required',
        'sucursal_id' => 'required'
    ]);
    
    // Find the user
    $user = User::where('email', $request->email)->first();
    
    // Check user credentials
    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withErrors(['email' => 'Las credenciales proporcionadas son incorrectas.']);
    }
    
    // Verificar si ya existe un registro de hoy
    $todayCheckInOut = CheckInCheckOut::where('user_id', $user->id)
            ->where('sucursal_id', $request->sucursal_id) 
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
    
    
    // Si no hay registro o ya tiene un check-out, crear un nuevo check-in
    if (!$todayCheckInOut || $todayCheckInOut->check_out) {
        // Check-in
        $checkInOut = new CheckInCheckOut();
        $checkInOut->user_id = $user->id;
        $checkInOut->sucursal_id = $user->sucursal_id;
        $checkInOut->estado = 'Presente';
        $checkInOut->check_in = now();
        $checkInOut->save();
        
        return redirect()->back()->with('success', 'Entrada registrada correctamente.');
    } else {
        // Check-out
        $todayCheckInOut->check_out = now();
        $todayCheckInOut->estado = 'Ausente';
        $todayCheckInOut->horas_trabajadas = $todayCheckInOut->check_in->diffInMinutes(now());
        $todayCheckInOut->save();
        
        return redirect()->back()->with('success', 'Salida registrada correctamente.');
    }

}




    // Registrar un check-in para un usuario
    public function checkIn(User $usuario, Sucursal $sucursal)
    {
        // Verifica si ya hay un registro de check-in activo para el usuario en esta sucursal
        $existingRecord = CheckInCheckOut::where('usuario_id', $usuario->id)
                                        ->where('sucursal_id', $sucursal->id)
                                        ->whereNull('check_out') // Asegura que no haya un check-out
                                        ->first();

        if ($existingRecord) {
            // Si ya existe un check-in sin check-out, muestra un error
            return redirect()->route('checador', $sucursal)
                            ->with('error', 'Ya tienes un check-in activo.');
        }

        // Si no hay un check-in activo, se crea uno nuevo
        CheckInCheckOut::create([
            'usuario_id' => $usuario->id,
            'sucursal_id' => $sucursal->id,
            'estado' => 'Presente',
            'check_in' => now(),
            'horas_trabajadas' => 0, // Inicializa en 0
        ]);

        // Redirecciona con un mensaje de éxito
        return redirect()->route('checador', $sucursal)
                        ->with('success', 'Check-in registrado con éxito.');
    }


    // Registrar un check-out para un usuario
    public function checkOut(Usuario $usuario, Sucursal $sucursal)
    {
        $registro = CheckInCheckOut::where('usuario_id', $usuario->id)
                                   ->where('sucursal_id', $sucursal->id)
                                   ->latest()
                                   ->first();

        if ($registro && $registro->check_out === null) {
            // Actualiza el check-out
            $registro->update(['check_out' => now()]);
            $registro->update(['estado' => 'Ausente']);

            // Calcular horas trabajadas
            $horasTrabajadas = $registro->check_out->diffInHours($registro->check_in);
            $registro->update(['horas_trabajadas' => $horasTrabajadas]);
        } else {
            return redirect()->route('checkin_checkout.index', $sucursal)
                             ->with('error', 'No hay un check-in activo para este usuario.');
        }

        return redirect()->route('checkin_checkout.index', $sucursal)
                         ->with('success', 'Check-out registrado con éxito.');
    }
}
