<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class UpdatePassword extends Controller
{
    public function test(Request $request) {
        return response()->json([
            'message' => 'Token validados',
        ], Response::HTTP_OK);
    }
}
