<?php

use App\Models\User;
use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\ReserveController;
use App\Http\Controllers\SportController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\RedirectResponse;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//  NO necesita autenticación
// ---------------------------

// #####################
// ## USER CONTROLLER ##
// #####################

// -- Obtener usuario por id --
Route::get('get-user/{id}', [UserController::class, 'getUserById']);

// #####################
// ## AUTH CONTROLLER ##
// #####################

// -- Envio de email para recuperar la contraseña --  
Route::get('forgot-password/{email}', function ($email) {
    $token = Str::random(80);

    $user = User::where('email', $email)->firstOrFail();
    
    // Guardando el token generado en el usuario correspondiente
    if ($user) {
        $user->token_password_reset = $token;
        $user->save();
        // Envio de email
        Mail::to($email)->send(new \App\Mail\PasswordReset($email, $token));
        return response()->json(["msg"=>"Se ha enviado un correo para cambiar tu contraseña"], Response::HTTP_ACCEPTED);
    } 

    return response()->json(["msg"=>"El email no existe"], Response::HTTP_NOT_FOUND);
});

// -- Validación del token enviado por parámetro y el token generado -- 
Route::get('validation-token/{email}/{get_token}', function ($email, $get_token) {
    $user = User::where('email', $email)->firstOrFail();
    
    if ($user && $user->token_password_reset == $get_token) {
        return new RedirectResponse('http://localhost:4200/update-password/' . $email . "/". $get_token);
    }
    
    return response()->json(["msg"=>"Error al validar el email"], Response::HTTP_NOT_FOUND);
    
});

// -- Iniciar sesión --
Route::post('login', [AuthController::class, 'authenticate']);

// -- Registro --
Route::post('register', [AuthController::class, 'register']);

//  -- Actualización de la contraseña -- 
Route::put('update-password/{email}/{token}', [AuthController::class, 'updatePassword']);


// ######################
// ## COURT CONTROLLER ##
// ######################

// -- Obtener todas las pistas --
Route::get('get-courts', [CourtController::class, 'getCourts']);

// -- Obtener una pista por su id --
Route::get('get-court/{id}', [CourtController::class, 'getCourt']);

// -- Obtener likes y dislikes de una pista por sus comentarios --
Route::get('get-likes-and-dislikes/{id}', [CourtController::class, 'getLikesAndDislikes']);


// ########################
// ## COMMENT CONTROLLER ##
// ########################

// -- Obtener comentarios de una pista --
Route::get('get-comments/{court_id}', [CommentController::class, 'getComments']);

// ######################
// ## FLOOR CONTROLLER ##
// ######################

// -- Obtener tipos de suelo --
Route::get('get-floors', [FloorController::class, 'getFloors']);

// -- Obtener suelo por su id --
Route::get('get-floor/{id}', [FloorController::class, 'getFloor']);


// ######################
// ## SPORT CONTROLLER ##
// ######################

// -- Obtener deportes --
Route::get('get-sports', [SportController::class, 'getSports']);

// -- Obtener deporte por su id --
Route::get('get-sport/{id}', [SportController::class, 'getSport']);

// Necesita autenticación
// -----------------------
Route::group(['middleware' => ['jwt.verify']], function() {
    // #####################
    // ## AUTH CONTROLLER ##
    // #####################
    
    // -- Desconectar sesion --
    Route::post('logout', [AuthController::class, 'logout']);

    // -- Obtener usuario --
    Route::post('get-user', [AuthController::class, 'getUser']);
    
    
    // ######################
    // ## ADMIN CONTROLLER ##
    // ######################
    
    // -- Obtener todos los usuarios --
    Route::get('get-users', [AdminController::class, 'getUsers']);

    // -- Obtener advertencias de un usuario por su id --
    Route::get('get-warnings/{id}', [AdminController::class, 'getWarningsById']);

    // -- Obtener rol de un usuario por su id --
    Route::get('get-role/{id}', [AdminController::class, 'getRoleById']);

    // -- Obtener usuario por su id (Admin) --
    Route::post('get-user/{id}', [AdminController::class, 'getUser']);
    
    // -- Añadir advertencia --
    Route::post('add-warning/{id}', [AdminController::class, 'addWarning']);

    // -- Editar nombre de usuario por su id (Admin) --
    Route::put('edit-user/{id}', [AdminController::class, 'editUser']);
    
    // -- Activar o desactivar cuenta de usuario --
    Route::put('active-desactive-account/{user_id}', [AdminController::class, 'activeDesactiveAccount']);

    // -- Editar correo del usuario por su id (Admin) --
    Route::put('edit-email/{id}', [AdminController::class, 'editEmail']);

    // -- Editar rol de un usuario por su id (Admin) --
    Route::put('update-role/{user_id}', [AdminController::class, 'updateRole']);

    // -- Borrar cuenta de usuario por su id (Admin) --
    Route::delete('delete-account/{id}', [AdminController::class, 'delAccount']);

    
    // #####################
    // ## USER CONTROLLER ##
    // #####################

    // -- Obtener advertencias --
    Route::get('get-warnings', [UserController::class, 'getWarnings']);

    // -- Obtener rol del usuario --
    Route::get('get-role', [UserController::class, 'getRole']);

    // -- Obtener foto de perfil --
    Route::get('get-image', [UserController::class, 'getImage']);

    // -- Actualizar foto de perfil --
    Route::post('upload-image', [UserController::class, 'uploadImage']);

    // -- Actualizar foto de perfil por el id del usuario --
    Route::post('upload-image/{id}', [UserController::class, 'uploadImageById']);

    // -- Editar nombre de usuario --
    Route::put('edit-user', [UserController::class, 'editUser']);

    // -- Editar correo del usuario --
    Route::put('edit-email', [UserController::class, 'editEmail']);

    // -- Borrar cuenta de usuario --
    Route::delete('delete-account', [UserController::class, 'delAccount']);

    // -- Eliminar foto de perfil --
    Route::delete('delete-image', [UserController::class, 'deleteImage']);

    // -- Eliminar foto de perfil por el id del usuario --
    Route::delete('delete-image/{id}', [UserController::class, 'deleteImageById']);

    
    // ######################
    // ## COURT CONTROLLER ##
    // ######################

    // -- Añadir pista --
    Route::post('add-court', [CourtController::class, 'addCourt']);

    // -- Actualizar foto de la pista --
    Route::post('add-image/{id}', [CourtController::class, 'addImage']);

    // -- Borrar foto de la pista --
    Route::post('remove-image/{id}', [CourtController::class, 'removeImage']);
    
    // -- Obtener todos los usuarios --
    Route::put('edit-court/{id}', [CourtController::class, 'editCourt']);

    // -- Eliminar una pista por su id --
    Route::delete('delete-court/{id}', [CourtController::class, 'deleteCourt']);
    
    
    // ########################
    // ## COMMENT CONTROLLER ##
    // ########################

    // -- Añadir comentario a una pista --
    Route::post('add-comment/{court_id}', [CommentController::class, 'addComment']);

    // -- Eliminar comentario de una pista --
    Route::delete('delete-comment/{id}', [CommentController::class, 'deleteComment']);
    
    // ########################
    // ## RESERVE CONTROLLER ##
    // ########################

    // -- Obtener reservas --
    Route::get('get-bookings', [ReserveController::class, 'getReserves']);
    
    // -- Obtener reservas de un usuario --
    Route::get('get-booking-user/{user_id}', [ReserveController::class, 'getBookingUser']);

    // -- Obtener reservas de una pista --
    Route::get('get-booking-court/{court_id}', [ReserveController::class, 'getBookingCourt']);

    // -- Comprobar si existe una reserva por el id de la pista y del usuario --
    Route::get('exists-reserve/{court_id}/{user_id}', [ReserveController::class, 'existsReserve']);

    // -- Añadir una reserva --
    Route::post('add-reserve', [ReserveController::class, 'addReserve']);
    
    // -- Eliminar una reserva --
    Route::delete('delete-reserve/{id}', [ReserveController::class, 'deleteReserve']);


});