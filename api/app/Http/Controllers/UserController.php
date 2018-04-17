<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\User;
use App\Setting;

class UserController extends Controller
{

    public function __construct()
    {
        //  $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request - data sent by http request: $request - user login data
     * @return \Illuminate\Http\Response array JSON
     */
    public function autentificare(Request $request)
    {
        // validate data
        $this->validate($request, [
            'email' => 'required',
            'parola' => 'required'
        ]);

        $userModel = User::where('email', $request->input('email'))->first();

        if(Hash::check($request->input('parola'), $userModel->parola)){

            $expireApiKeyTime = Setting::where('key', 'api.api_key_expire')->first()->value;
            $apikey = base64_encode(str_random(40));
            User::where('email', $request->input('email'))->update(['api_key' => $apikey, 'api_key_expire' => Carbon::now()->addMinutes($expireApiKeyTime)]);

            return response()->json(['api_key' => encrypt($apikey)]);
        } else {

            return response()->json(['status' => 'fail'],401);
        }
    }
}