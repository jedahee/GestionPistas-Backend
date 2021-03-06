<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use JWTAuth;
use App\Models\User;
use Exception;
use File;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
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
    #                 AÑADIR ADVERTENCIA              #
    ###################################################
    */

    /**
    * @OA\Post(
    *     security={{"bearerAuth":{}}},
    *     path="/api/add-warning/{id}",
    *     tags = {"Admin"},
    *     summary="Añadir una advertencia a un usuario",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="adv",
    *        in="header",
    *        description="El texto de advertencia para el usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Operación realizada con éxito
    *           Primera advertencia añadida con éxito
    *           Segunda advertencia añadida con éxito. La cuenta ha sido bloqueada"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           No se encuentra el usuario
    *           Error de validacion",
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Esta operación solo lo puede hacer un administrador o un moderador"
    *     ),
    * )
    */
    public function addWarning(Request $request, $id) {
        if ($this->user->rol_id == 1 || $this->user->rol_id == 2) {
            $data = $request->only('adv');

            $validator = Validator::make($data, [
                'adv' => 'required|min:5|max:100|string',
            ]);

            if ($validator->fails())
                return response()->json(['error' => $validator->messages()], Response::HTTP_BAD_REQUEST);
            
            try {
                $user = User::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'No se encuentra el usuario'
                ], Response::HTTP_BAD_REQUEST);        
            }

            if ($user->numAdvertencias == 0) {
                $user->adv1 = $request->adv;
                $user->numAdvertencias = 1;
                $user->save();

                return response()->json([
                    'msg' => 'Primera advertencia añadida con éxito'
                ], Response::HTTP_ACCEPTED);

            } else if ($user->numAdvertencias == 1) {
                $user->adv2 = $request->adv;
                $user->numAdvertencias = 2;
                $user->activo = 0;

                $user->save();
                
                
                return response()->json([
                    'msg' => 'Segunda advertencia añadida con éxito. La cuenta ha sido bloqueada'
                ], Response::HTTP_ACCEPTED);
            }

            $user->save();
        }

        return response()->json([
            'msg' => 'Esta operación solo lo puede hacer un administrador o un moderador'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #                   OBETENER ROL                  #
    ###################################################
    */
    /**
    * @OA\Get(
    *     path="/api/get-role/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Usuario"},
    *     summary="Obtener el rol del usuario por su id",
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $rol_id (integer)"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           $Este usuario no existe"
    *     ),
    * )
    */
    public function getRoleById(Request $request, $id) {
        try {
            $user = User::findOrFail($id);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Este usuario no existe'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'rol_id' => $user->rol_id,
        ], Response::HTTP_ACCEPTED);

    }

    /*
    ###################################################
    #               OBETENER ADVERTENCIAS             #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-warnings/{id}",
    *     tags = {"Admin"},
    *     security={{"bearerAuth":{}}},
    *     summary="Obtener las advertencias del usuario puestas por algún Moderador / Administrador",
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $adv1 (string)
    *           $adv2 (string)"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Este usuario no existe"
    *     ),
    * )
    */
    public function getWarningsById(Request $request, $id) {
        try {
            $user = User::findOrFail($id);
        } catch (Exception $e) {
            return response()->json([
                'msg' => 'Este usuario no existe'
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'adv1' => $user->adv1,
            'adv2' => $user->adv2,
        ], Response::HTTP_ACCEPTED);

    }

    /*
    ###################################################
    #                OBTENER USUARIOS                 #
    ###################################################
    */

    /**
    * @OA\Get(
    *     path="/api/get-users",
    *     tags = {"Admin"},
    *     summary="Obtener todos los usuarios",
    *     security={{"bearerAuth":{}}},
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se han obtenido los usuarios correctamente
    *           $users (Object)"
    *     ),
    * )
    */
    public function getUsers(Request $request) {
        if ($this->user->rol_id == 1 || $this->user->rol_id == 2) {
            $users = User::all();

            return response()->json([
                'msg' => 'Se han obtenido los usuarios correctamente',
                'users' => $users
            ], Response::HTTP_ACCEPTED);
        }
    }

    /*
    ###################################################
    #                OBTENER USUARIO                  #
    ###################################################
    */

    /**
    * @OA\Post(
    *     path="/api/get-user/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Admin"},
    *     summary="Obtener usuario y ver información de este",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           $user (Object)"
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Este usuario no existe"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Esta operación solo lo puede hacer un administrador o un moderador"
    *     )
    * )
    */
    public function getUser(Request $request, $id) {
        if ($this->user->rol_id == 1 || $this->user->rol_id == 2) {
            try {
                $user = User::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Este usuario no existe'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            //Devolvemos los datos del usuario si todo va bien.
            return response()->json(['user' => $user], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'msg' => 'Esta operación solo lo puede hacer un administrador o un moderador'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #                 ELIMINAR CUENTA                 #
    ###################################################
    */
    /**
    * @OA\Delete(
    *     path="/api/delete-account/{id}",
    *     tags = {"Admin"},
    *     security={{"bearerAuth":{}}},
    *     summary="Borrar cuenta de un usuario",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha eliminado la cuenta correctamente"    
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Este usuario no existe"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Esta operación solo lo puede hacer un administrador"
    *     ),
    *     @OA\Response(
    *         response=406,
    *         description="
    *           No se ha podido eliminar la cuenta"
    *     ),
    * )
    */
    public function delAccount(Request $request, $id) {
        if ($this->user->rol_id == 1) {
            try {
                $user = User::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Este usuario no existe'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            if ($user->delete()) {
                return response()->json([
                    'msg' => 'Se ha eliminado la cuenta correctamente'
                ], Response::HTTP_ACCEPTED);
            }

            return response()->json([
                'msg' => 'No se ha podido eliminar la cuenta'
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json([
            'msg' => 'Esta operación solo lo puede hacer un administrador'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #           ACTUALIZAR NOMBRE DE USUARIO          #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/edit-user/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Admin"},
    *     summary="Actualizar nombre de un usuario",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="nombre",
    *        in="query",
    *        description="Nombre a actualizar",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha actualizado correctamente el nombre de usuario"    
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Este usuario no existe"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Esta operación solo lo puede hacer un administrador"
    *     ),
    *     @OA\Response(
    *         response=406,
    *         description="
    *           No se ha podido actualizar el nombre de usuario"
    *     ),
    * )
    */
    public function editUser(Request $request, $id) {
        if ($this->user->rol_id == 1) {
            try {
                $user = User::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Este usuario no existe'
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->validate($request, [
                'nombre' => 'required|string|max:30',
            ]);
            
            if ($user->update(['nombre' => $request->nombre])) {
                return response()->json([
                    'msg' => "Se ha actualizado correctamente el nombre de usuario",
                ], Response::HTTP_ACCEPTED);
            }

            return response()->json([
                'msg' => "No se ha podido actualizar el nombre de usuario",
            ], Response::HTTP_NOT_ACCEPTABLE);
        }
        return response()->json([
            'msg' => 'Esta operación solo lo puede hacer un administrador'
        ], Response::HTTP_FORBIDDEN);

    } 
        
    /*
    ###################################################
    #                 ACTUALIZAR CORREO               #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/edit-email/{id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Admin"},
    *     summary="Actualizar correo electrónico de un usuario",
    *     @OA\Parameter(
    *        name="id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="email",
    *        in="query",
    *        description="Email a actualizar",
    *        required=true,
    *        @OA\Schema(
    *            type="string"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha actualizado correctamente el email del usuario"    
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Este usuario no existe"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Esta operación solo lo puede hacer un administrador"
    *     ),
    *     @OA\Response(
    *         response=406,
    *         description="
    *           No se ha podido actualizar correctamente el email del usuario"
    *     ),
    * )
    */

    public function editEmail(Request $request, $id) {
        if ($this->user->rol_id == 1) {
            try {
                $user = User::findOrFail($id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Este usuario no existe'
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->validate($request, [
                'email' => 'required|email',
            ]);

            if ($user->update(['email' => $request->email])) {
                return response()->json([
                    'msg' => "Se ha actualizado correctamente el email del usuario",
                ], Response::HTTP_ACCEPTED);
            }
            
            return response()->json([
                'msg' => "No se ha podido actualizar correctamente el email del usuario",
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json([
            'msg' => 'Esta operación solo lo puede hacer un administrador'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #           ACTIVAR O DESACTIVAR CUENTA           #
    ###################################################
    */

    /**
    * @OA\Put(
    *     path="/api/active-desactive-account/{user_id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Admin"},
    *     summary="Activar o desactivar cuenta de usuario",
    *     @OA\Parameter(
    *        name="user_id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="activo",
    *        in="query",
    *        description="Parámetro que define si el usuario esta activo o no",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha actualizado correctamente el usuario"    
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Este usuario no existe"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Esta operación solo lo puede hacer un administrador"
    *     ),
    *     @OA\Response(
    *         response=406,
    *         description="
    *           Valor no permitido"
    *     ),
    * )
    */

    public function activeDesactiveAccount(Request $request, $user_id) {
        if ($this->user->rol_id == 1) {
            try {
                $user = User::findOrFail($user_id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Este usuario no existe'
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->validate($request, [
                'activo' => 'required|integer',
            ]);

            if ($request->activo == 0 || $request->activo == 1) {
                $user->activo = $request->activo;
                $user->save();
                return response()->json([
                    'msg' => 'Se ha actualizado correctamente el usuario',
                ], Response::HTTP_ACCEPTED);
            }
            
            return response()->json([
                'msg' => 'Valor no permitido',
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json([
            'msg' => 'Esta operación solo lo puede hacer un administrador'
        ], Response::HTTP_FORBIDDEN);
    }

    /*
    ###################################################
    #          ACTUALIZAR ROL DE UN USUARIO           #
    ###################################################
    */
    /**
    * @OA\Put(
    *     path="/api/update-role/{user_id}",
    *     security={{"bearerAuth":{}}},
    *     tags = {"Admin"},
    *     summary="Actualizar rol de un usuario",
    *     @OA\Parameter(
    *        name="user_id",
    *        in="path",
    *        description="ID del usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Parameter(
    *        name="rol_id",
    *        in="query",
    *        description="El rol que va a tomar el usuario",
    *        required=true,
    *        @OA\Schema(
    *            type="integer"
    *        )
    *     ),
    *     @OA\Response(
    *         response=202,
    *         description="
    *           Se ha actualizado correctamente el usuario"    
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="
    *           Este usuario no existe"
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="
    *           Esta operación solo lo puede hacer un administrador"
    *     ),
    *     @OA\Response(
    *         response=406,
    *         description="
    *           Valor no permitido"
    *     ),
    * )
    */

    public function updateRole(Request $request, $user_id) {
        if ($this->user->rol_id == 1) {
            try {
                $user = User::findOrFail($user_id);
            } catch (Exception $e) {
                return response()->json([
                    'msg' => 'Este usuario no existe'
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->validate($request, [
                'rol_id' => 'required|integer',
            ]);

            if ($request->rol_id >= 1 && $request->rol_id <= 3) {
                $user->rol_id = $request->rol_id;
                $user->save();
                return response()->json([
                    'msg' => 'Se ha actualizado correctamente el usuario',
                ], Response::HTTP_ACCEPTED);
            }
            
            return response()->json([
                'msg' => 'Valor no permitido',
            ], Response::HTTP_NOT_ACCEPTABLE);
        }

        return response()->json([
            'msg' => 'Esta operación solo lo puede hacer un administrador'
        ], Response::HTTP_FORBIDDEN);
    }
}
