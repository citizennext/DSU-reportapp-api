<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Auth;

use App\Unitate;
use App\Departament;
use App\Audit;
use App\User;

class DepartamentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all active departamente
     * Browse our Data Type (B)READ
     *
     * @return array JSON
     */
    public function index()
    {
        if(Auth::user()->hasPermission('browse_departamente')){
            $collection = Departament::where('deleted_at', null)->get();
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Get individual record Departament, by ID
     * Read our Data Type B(R)EAD
     *
     * @param integer $id - Departament ID
     * @return array JSON
     */
    public function find($id)
    {
        if(Auth::user()->hasPermission('read_departamente')){
            $collection = Departament::find($id);
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Create departament.
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
            if(Auth::user()->hasPermission('add_departamente')){
                // validate data
                $this->validate($request, [
                    'nume' => 'required',
                    'telefon' => 'required',
                ]);

                // create departament
                $collection = Departament::create($request->all());
                $result['message'] = 'success';
                $result['description'] = 'Departamentul [' . $collection['nume'] .'] creat.';
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /departament/adaugare',
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
     * Edit individual departament.
     * Edit our Data Type BR(E)AD
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
    public function edit(Request $request)
    {
        $result = array();

        try{
            if($departamentModel = Departament::find($request->input('request_id'))){
                if(Auth::user()->hasPermission('edit_departamente')){
                    $requestOld = $departamentModel->toArray();
                    $requestData = $request->all();
                    unset($requestData['id'], $requestData['request_id'], $requestData['_url']);

                    $departamentModel->update($requestData);
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
                        'description' => 'Departamentul [' . $departamentModel->nume . '] modificat cu succes.',
                        'old_value' => $dataOld,
                        'new_value' => $dataChanged,
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'success';
                    $result['description'] = 'Departamentul [' . $departamentModel->nume . '] modificat cu succes.';
                } else {
                    // add a audit log
                    $auditLog = array(
                        'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                        'new_value' => '401 /departament/editare',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'fail';
                    return response()->json($result, 401);
                }
            } else {
                $result['message'] = 'fail';
                $result['description'] = 'Departament inexistent.';
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Delete departament - soft.
     * Delete our Data Type BREA(D)
     *
     * @param integer $id - Departament ID
     * @return array JSON
     */
    public function delete($id)
    {
        $result = array();

        try {
            if(Auth::user()->hasPermission('delete_departamente')) {
                // soft delete departament
                $departamentModel = Departament::find($id);
                $count = Departament::destroy($id);

                if($count === 1)
                {
                    // soft delete all unitati related to this departament, and all users related to these unitati
                    $countUnitati = Unitate::where('departament_id', '=', $id)->get()->toArray();
                    if(count($countUnitati) > 0) {
                        foreach ($countUnitati as $unitate) {
                            Unitate::where('id', '=', $unitate['id'])->update(['deleted_at' => Carbon::now()]);
                            User::where('unitate_id', '=', $unitate['id'])->update(['deleted_at' => Carbon::now()]);
                        }
                    }


                    $auditLog = array(
                        'description' =>  'Departamentul [' . $departamentModel->nume . '] sters cu succes.',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);

                    $result['message'] = 'success';
                    $result['description'] = 'Departamentul [' . $departamentModel->nume . '] sters cu succes.';
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Departamentul nu poate fi sters. Probabil nu exista.';
                }
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /departament/stergere',
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
