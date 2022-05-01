<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use App\Models\User;
use App\Models\Reserve;
use App\Models\Court;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ReserveController extends Controller
{
    protected $user;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        if($token != '')
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    /*
    ###################################################
    #                OBTENER RESERVAS                 #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-bookings",
    *     tags = {"Reservas"},
    *     summary="Obtener todas las reservas registradas",
    *     @OA\Response(
    *         response=200,
    *         description="Se devuelven todas las reservas"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="Necesitas ser Administrador para realizar esta operación"
    *     )
    * )
    */
    public function getReserves(Request $request)
    {
        if ($this->user->rol_id == 1) {
            $bookings = Reserve::all();

            return response()->json([
                'bookings' => $bookings
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'msg' => 'Necesitas ser Administrador para realizar esta operación'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #                AÑADIOR RESERVA                  #
    ###################################################
    */
    /**
    * @OA\Post(
    *     path="/api/add-reserve",
    *     tags = {"Reservas"},
    *     summary="Registra una nueva reserva",
    *     @OA\Response(
    *         response=200,
    *         description="Reserva añadida con éxito"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Falló validación"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Esta reserva debe tener un número de lista y no un horario"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Esta reserva debe tener un horario y no un número de lista"
    *     )
    * )
    */
    public function addReserve(Request $request)
    {
        $data = $request->only('horaInicio', 'horaFinalizacion', 'numLista', 'pistas_id', 'users_id');

        $validator = Validator::make($data, [
            'horaInicio' => 'nullable|string',
            'horaFinalizacion' => 'nullable|string',
            'numLista' => 'nullable|integer',
            'pistas_id' => 'required|integer',
            'users_id' => 'required|integer',
        ]);

        if ($validator->fails())
            return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
        else {
            if ($request->numLista != null && $request->horaInicio == null && $request->horaFinalizacion == null) {
                $reserve = Reserve::create([
                    'horaInicio' => $request->horaInicio,
                    'horaFinalizacion' => $request->horaFinalizaicon,
                    'numLista' => $request->numLista,
                    'pistas_id' => $request->pistas_id,
                    'users_id' => $request->users_id,
                ]);

                return response()->json([
                    'msg' => 'Reserva añadida con éxito',
                    'reserve' => $reserve
                ], Response::HTTP_ACCEPTED);
            } else {
                return response()->json([
                    'msg' => 'Esta reserva debe tener un número de lista y no un horario',
                ], Response::HTTP_BAD_REQUEST);
            }
        

            if ($request->numLista == null && $request->horaInicio != null && $request->horaFinalizacion != null) {
                $reserve = Reserve::create([
                    'horaInicio' => $request->horaInicio,
                    'horaFinalizacion' => $request->horaFinalizaicon,
                    'numLista' => $request->numLista,
                    'pistas_id' => $request->pistas_id,
                    'users_id' => $request->users_id,
                ]);

                return response()->json([
                    'msg' => 'Reserva añadida con éxito',
                    'reserve' => $reserve
                ], Response::HTTP_ACCEPTED);
            } else {
                return response()->json([
                    'msg' => 'Esta reserva debe tener un horario y no un número de lista',
                ], Response::HTTP_BAD_REQUEST);
            }
                
        
        }
    }

    /*
    ###################################################
    #                 EXISTE RESERVA                  #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/exists-reserve/court_id/user_id",
    *     tags = {"Reservas"},
    *     summary="Comprueba si existe una reserva",
    *     @OA\Response(
    *         response=200,
    *         description="True"
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="False"
    *     )
    * )
    */
    public function existsReserve(Request $request, $court_id, $user_id)
    {

        $booking = Reserve::where('pistas_id', $court_id)->where('users_id', $user_id)->get();
        if (count($booking) != 0) {
            return response()->json([
                'booking' => $booking,
                'exists' => true
            ], Response::HTTP_ACCEPTED); 
        }

        return response()->json([
            'exists' => false
        ], Response::HTTP_NOT_FOUND);
    }   
    
}
