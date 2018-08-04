<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Mail\ActivareUtilizator;
use App\Mail\WelcomeUtilizator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;
use Auth;
use Validator;

use App\User;
use App\Unitate;
use App\Setting;
use App\Audit;
use App\Mail\ForgotPassword;

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
        $this->middleware('auth', ['except' => ['autentificare', 'activare', 'edit', 'forgotPassword']]);
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
            $rules = [
                'email' => 'required',
                'parola' => 'required'
            ];
            $messages = [
                'email.required' => 'Adresa de e-mail este necesara.',
                'parola.required' => 'Parola este necesara.',
            ];

            $validator = Validator::make($request->json()->all(), $rules, $messages);
            if ($validator->fails()) {

                $result['message'] = 'fail';
                $result['description'] = $validator->errors();
                return response()->json($result, 401);

            } else {
                // check if email exist
                if(User::where('email', $request->input('email'))->first()) {

                    $userModel = User::where('email', $request->input('email'))->first();

                    // check if user is active (soft delete)
                    if ($userModel->active == 0) {

                        $result['message'] = 'fail';
                        $result['description'] = 'Utilizatorul [' . $userModel['email'] . '] este inactiv';
                        return response()->json($result)->setStatusCode(493, 'Fail utilizator');

                    } elseif (Hash::check($request->input('parola'), $userModel->parola)) {

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
                        $result['description'] = 'Parola utilizatorului [' . $userModel['email'] . '] este incorecta.';
                        return response()->json($result)->setStatusCode(492, 'Fail parola');
                    }
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Nu exista nici un utilizator cu aceasta adresa de e-mail [' . $request->input('email') . '].';
                    return response()->json($result)->setStatusCode(491, 'Fail e-mail');
                }
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
     * Read our Data Type B(R)EAD
     *
     * @param integer $id - User ID
     * @return array JSON
     */
    public function find($id)
    {
        $result = array();

        try{
            if(Auth::user()->hasPermission('read_users')){
                $collection = User::with(['rol', 'unitate.departament', 'unitate.parent'])->find($id);
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
     * Read our Data Type B(R)EAD
     *
     * @param string $email - User email
     * @return array JSON
     */
    public function findByEmail($email)
    {
        $result = array();

        try{
            if(Auth::user()->hasPermission('read_users')){
                $collection = User::with(['rol', 'unitate.departament', 'unitate.parent'])->where('email', $email)->first();
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
     * Get individual user.
     * Read our Data Type B(R)EAD
     *
     * @return array JSON
     */
    public function profil()
    {
        $result = array();

        try{
            if(Auth::user()->hasPermission('read_users')){
                $collection = User::with(['rol', 'unitate.departament', 'unitate.parent'])->find(Auth::user()->id);
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
     * Create user.
     * Add Data Type BRE(A)D
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     * @throws \Illuminate\Validation\ValidationException
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
                Mail::to($collection['email'])->send(new ActivareUtilizator($collection['id'], $inputPass));

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
                    'new_value' => '401 /users/adaugare',
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
     * Activate user.
     *
     * @param string $token - User remember token
     * @return array JSON
     */
    public function activare($token)
    {
        $result = array();

        try{
            $user = User::where('remember_token', $token)->first();
            if(!empty($user)) {
                // update user
                $user->update(['active' => 1, 'remember_token' => null]);

                // add a audit log
                $auditLog = array(
                    'description' => 'Utilizator activat',
                    'new_value' => 'Utilizatorul [' . $user->email . '] a fost activat cu succes.',
                    'user_id' => $user->id
                );
                Audit::create($auditLog);

                // send welcome mail
                Mail::to($user->email)->send(new WelcomeUtilizator($user->id));

                $result['message'] = 'success';
                $result['description'] = 'Contul a fost activat cu succes.';
            } else {
                $result['message'] = 'fail';
                $result['description'] = 'Utilizator activat deja sau inexistent.';
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Edit individual user.
     * Edit our Data Type BR(E)AD
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
    public function edit(Request $request)
    {
        $result = array();

        try{
            if($userModel = User::find($request->input('id'))){
                if($userModel->id === Auth::user()->id || Auth::user()->hasPermission('edit_users')){
                    $requestOld = $userModel->toArray();
                    $requestData = $request->all();
                    unset($requestData['id'], $requestData['_url']);
                    $userModel->update($requestData);
                    // add a audit log
                    $dataOld = '';
                    $dataChanged = '';
                    foreach($requestData as $key=>$value){
                        $dataOld .= $key . ' = ' . $requestOld[$key] . ', ';
                        $dataChanged .= $key . ' = ' . $value . ', ';
                    }
                    $dataOld = substr($dataOld, 0, -2);
                    $dataChanged = substr($dataChanged, 0, -2);
                    $auditLog = array(
                        'description' => 'Utilizatorul [' . $userModel->email . '] modificat cu succes.',
                        'old_value' => $dataOld,
                        'new_value' => $dataChanged,
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'success';
                    $result['description'] = 'Utilizatorul [' . $userModel->email . '] modificat cu succes.';
                } else {
                    // add a audit log
                    $auditLog = array(
                        'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                        'new_value' => '401 /users/editare',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'fail';
                    return response()->json($result, 401);
                }
            } else {
                $result['message'] = 'fail';
                $result['description'] = 'Utilizator inexistent.';
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Delete user.
     * Delete our Data Type BREA(D)
     *
     * @param integer $id - User ID
     * @return array JSON
     */
    public function delete($id)
    {
        $result = array();

        try {
            if(Auth::user()->hasPermission('delete_users')) {
                // soft delete user
                $userModel = User::find($id);
                $count = User::destroy($id);

                if($count === 1)
                {
                    // soft delete all audits records
                    Audit::where('user_id', '=', $id)->update(['deleted_at' => Carbon::now()]);

                    $auditLog = array(
                        'description' =>  'Utilizatorul [' . $userModel->email . '] sters cu succes.',
                        'new_value' => Carbon::now(),
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);

                    $result['message'] = 'success';
                    $result['description'] = 'Utilizatorul [' . $userModel->email . '] sters cu succes.';
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Utilizatorul [' . $userModel->email . '] nu poate fi sters. Probabil nu exista.';
                }
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /users/stergere',
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
     * Reset password for individual user.
     * Edit our Data Type BR(E)AD
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
    public function resetPassword(Request $request)
    {
        $result = array();

        try{
            if(Auth::user()){
                User::where('id', '=', Auth::user()->id)->update(['parola' => Hash::make($request->input('parola'))]);

                $auditLog = array(
                    'description' => 'Parola utilizatorului [' . Auth::user()->email . '] modificata cu succes.',
                    'new_value' => 'password reset',
                    'user_id' => Auth::user()->id
                );
                Audit::create($auditLog);
                $result['message'] = 'success';
                $result['description'] = 'Parola utilizatorului [' . Auth::user()->email . '] modificata cu succes.';
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /users/resetare-parola',
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

    public function forgotPassword(Request $request)
    {
        $result = array();

        try{
            $user = User::where('email', $request->input('email'))->first();

            if (empty($user)) {
              $result['message'] = 'fail';
              $result['description'] = 'Nu exista niciun utilizator cu aceasta adresa de email';
              return response()->json($result);
            }

            $token = Password::createToken($user);

            Mail::to($user->email, $user->prenume)->send(new ForgotPassword($user, $token));

            $auditLog = array(
                'description' => 'Utilizatorul [' . $user->email . '] a cerut recuperarea parolei.',
                'new_value' => 'forgot password',
                'user_id' => $user->id
            );
            Audit::create($auditLog);

            $result['message'] = 'success';
            $result['description'] = 'Ai primit un email cu instructiuni pentru resetarea parolei';
            return response()->json($result);

        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }
}
