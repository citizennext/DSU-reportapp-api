<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Auth;

use App\Unitate;
use App\Departament;
use App\Audit;

class UnitateController extends Controller
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
     * Get all active unitati, including parent, departament
     * Browse our Data Type (B)READ
     *
     * @return array JSON
     */
    public function index()
    {
        if(Auth::user()->hasPermission('browse_unitati')){
            $collection = Unitate::with(['parent', 'departament'])->where('deleted_at', null)->get();
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Get all active localitati related to one unitate - parent, by parent id
     * Browse our Data Type (B)READ
     *
     * @param string $id - Unitate id
     * @return array JSON
     */
    public function unitatiByParent($id)
    {
        if(Auth::user()->hasPermission('browse_unitati')){
            $collection = Unitate::with('departament')->where(['parent_id' => $id, 'deleted_at' => null])->get();
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Get individual record Unitate, by ID
     * Read our Data Type B(R)EAD
     *
     * @param integer $id - Unitate ID
     * @return array JSON
     */
    public function find($id)
    {
        if(Auth::user()->hasPermission('read_unitati')){
            $collection = Unitate::with(['parent', 'departament'])->find($id);
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Create unitate.
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
            if(Auth::user()->hasPermission('add_unitati')){
                // validate data
                $this->validate($request, [
                    'nume' => 'required',
                    'departament_id' => 'required',
                    'telefon' => 'required',
                ]);

                // create localitate
                $collection = Unitate::create($request->all());
                $result['message'] = 'success';
                $result['description'] = 'Unitatea [' . $collection['nume'] .'] creata.';
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /unitate/adaugare',
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
     * Edit individual unitate.
     * Edit our Data Type BR(E)AD
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
    public function edit(Request $request)
    {
        $result = array();

        try{
            if($unitateModel = Unitate::find($request->input('request_id'))){
                if(Auth::user()->hasPermission('edit_unitati')){
                    $requestOld = $unitateModel->toArray();
                    $requestData = $request->all();
                    unset($requestData['id'], $requestData['request_id'], $requestData['_url']);

                    $unitateModel->update($requestData);
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
                        'description' => 'Unitatea [' . $unitateModel->nume . '] modificata cu succes.',
                        'old_value' => $dataOld,
                        'new_value' => $dataChanged,
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'success';
                    $result['description'] = 'Unitatea [' . $unitateModel->nume . '] modificata cu succes.';
                } else {
                    // add a audit log
                    $auditLog = array(
                        'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                        'new_value' => '401 /unitate/editare',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'fail';
                    return response()->json($result, 401);
                }
            } else {
                $result['message'] = 'fail';
                $result['description'] = 'Unitate inexistenta.';
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Delete unitate - soft.
     * Delete our Data Type BREA(D)
     *
     * @param integer $id - Unitate ID
     * @return array JSON
     */
    public function delete($id)
    {
        $result = array();

        try {
            if(Auth::user()->hasPermission('delete_localitati')) {
                // soft delete localitate
                $localitateModel = Localitate::find($id);
                $count = Localitate::destroy($id);

                if($count === 1)
                {
                    $auditLog = array(
                        'description' =>  'Localitatea [' . $localitateModel->nume . ' -> ' . $localitateModel->slug . '] stearsa cu succes.',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);

                    $result['message'] = 'success';
                    $result['description'] = 'Localitatea [' . $localitateModel->nume . ' -> ' . $localitateModel->slug . '] stearsa cu succes.';
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Localitatea nu poate fi stearsa. Probabil nu exista.';
                }
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /localitati/stergere',
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
