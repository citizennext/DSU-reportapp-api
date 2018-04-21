<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Mail\ActivareUtilizator;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Auth;

use App\User;
use App\Setting;
use App\Audit;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // set authorization only for specific methods
        $this->middleware('auth', ['except' => ['autentificare']]);
    }

    /**
     * User login - authenticate.
     *
     * @param Request $request - data sent by http request: $request - user login data
     * @return \Illuminate\Http\Response array JSON
     */
    public function autentificare(Request $request)
    {
        $result = array();

        try {
            // validate data
            $this->validate($request, [
                'email' => 'required',
                'parola' => 'required'
            ]);

            $userModel = User::where('email', $request->input('email'))->first();

            if($userModel->active == 0){

                $result['message'] = 'fail';
                $result['description'] = 'Utilizatorul [' . $userModel['email'] .'] este inactiv';

            } elseif(Hash::check($request->input('parola'), $userModel->parola)){

                $expireApiKeyTime = Setting::where('key', 'api.api_key_expire')->first()->value;
                $apikey = base64_encode(str_random(40));
                User::where('email', $request->input('email'))->update(['api_key' => $apikey, 'api_key_expire' => Carbon::now()->addMinutes($expireApiKeyTime)]);

                // add a audit log
                $auditLog = array(
                    'description' => 'Autentificare utilizator ' . (strlen($userModel->prenume) > 0 ? $userModel->prenume . ' ' . $userModel->nume : $userModel->nume),
                    'new_value' => 'Succes',
                    'user_id' => $userModel->id
                );
                Audit::create($auditLog);

                $result['message'] = 'success';
                $result['api_key'] = encrypt($apikey);

            } else {
                $result['message'] = 'fail';
                return response()->json($result, 401);
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * User logout - deauthenticate.
     *
     * @return \Illuminate\Http\Response array JSON
     */
    public function deautentificare()
    {
        $result = array();

        try{
            if(!empty(Auth::user())){
                Auth::user()->api_key = null;
                Auth::user()->api_key_expire = null;
                Auth::user()->save();

                // add a audit log
                $auditLog = array(
                    'description' => 'Deautentificare utilizator ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => 'Succes',
                    'user_id' => Auth::user()->id
                );
                Audit::create($auditLog);

                $result['message'] = 'success';
            } else {
                $result['message'] = 'fail';
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Get individual user.
     *
     * @param integer $id - User ID
     * @return array JSON
     */
    public function find($id)
    {
        $result = array();

        try{
            if(Auth::user()->hasPermission('read_users')){
                $collection = User::find($id);
                if(!empty($collection)) {
                    $result['message'] = 'success';
                    $result['user'] = $collection;
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Utilizator inexistent.';
                }
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /users/{id}',
                    'user_id' => Auth::user()->id
                );
                Audit::create($auditLog);
                $result['message'] = 'fail';
                return response()->json($result, 401);
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Get individual user by email.
     *
     * @param string $email - User email
     * @return array JSON
     */
    public function findByEmail($email)
    {
        $result = array();

        try{
            if(Auth::user()->hasPermission('read_users')){
                $collection = User::where('email', $email)->first();
                if(!empty($collection)) {
                    $result['message'] = 'success';
                    $result['user'] = $collection;
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Utilizator inexistent.';
                }
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /users/email/{email}',
                    'user_id' => Auth::user()->id
                );
                Audit::create($auditLog);
                $result['message'] = 'fail';
                return response()->json($result, 401);
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Create user.
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
    public function create(Request $request)
    {
        $result = array();

        try {
            if(Auth::user()->hasPermission('add_users')){
                // validate data
                $this->validate($request, [
                    'nume' => 'required',
                    'email' => 'required',
                    'role_id' => 'required',
                    'telefon_s' => 'required',
                    'unitate_id' => 'required',
                ]);
                // check if exist an user with similar token
                do {
                    $token = str_random(60);
                } while(User::where('remember_token', '=', $token)->count() > 0);
                // insert token in the request array
                $inputPass = str_random(12);
                $request->request->add(['remember_token' => $token, 'parola' => Hash::make($inputPass)]);

                // create user
                $collection = User::create($request->all());
                $result['message'] = 'success';
                $result['description'] = 'Utilizator [' . $collection['email'] .'] creat, dar inactiv';

                //send activation mail to user
                Mail::to($collection['email'])->send(new ActivareUtilizator($collection['id']));

                // add a audit log
                $auditLog = array(
                    'description' => 'Utilizator nou creat, inactiv',
                    'new_value' => 'Utilizator nou id[' . $collection['id'] . ']',
                    'user_id' => Auth::user()->id
                );
                Audit::create($auditLog);
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /users/create',
                    'user_id' => Auth::user()->id
                );
                Audit::create($auditLog);
                $result['message'] = 'fail';
                return response()->json($result, 401);
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }
}