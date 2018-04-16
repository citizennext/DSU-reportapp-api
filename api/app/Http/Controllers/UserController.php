<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use App\User;

class UserController extends Controller
{

    public function __construct()
    {
        //  $this->middleware('auth:api');
    }

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function authenticate(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'parola' => 'required'
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if(Hash::check($request->input('parola'), $user->parola)){

            $apikey = base64_encode(str_random(40));
            User::where('email', $request->input('email'))->update(['api_key' => "$apikey"]);

            return response()->json(['status' => 'success', 'api_key' => $apikey]);
        } else {

            return response()->json(['status' => 'fail'],401);
        }
    }
}