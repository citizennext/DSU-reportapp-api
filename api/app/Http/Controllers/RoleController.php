<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Auth;

use App\Role;
use App\User;
use App\Audit;

class RoleController extends Controller
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
     * Get all active roles
     * Browse our Data Type (B)READ
     *
     * @return array JSON
     */
    public function index()
    {
        if(Auth::user()->hasPermission('browse_roles')){
            $collection = Role::with('permisiuni')->where('deleted_at', null)->get();
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Get individual record Role, by ID
     * Read our Data Type B(R)EAD
     *
     * @param integer $id - Role ID
     * @return array JSON
     */
    public function find($id)
    {
        if(Auth::user()->hasPermission('read_roles')){
            $collection = Role::with('permisiuni')->find($id);
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Edit individual role.
     * Edit our Data Type BR(E)AD
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
    public function edit(Request $request)
    {
        $result = array();

        try{
            if($roleModel = Role::find($request->input('request_id'))){
                if(Auth::user()->hasPermission('edit_roles')){
                    $requestOld = $roleModel->toArray();
                    $requestData = $request->all();
                    unset($requestData['id'], $requestData['request_id'], $requestData['_url']);

                    $roleModel->update($requestData);
                    $roleModel->permisiuni()->sync($request->input('pivot'));
                    // add a audit log
                    $dataOld = '';
                    $dataChanged = '';
                    foreach($requestData as $key=>$value){
                        if($key !== 'pivot'){
                            $dataOld .= $key . ' = ' . $requestOld[$key] . ', ';
                            $dataChanged .= $key . ' = ' . $value . ', ';
                        }
                    }
                    $dataOld = substr($dataOld, 0, -2);
                    $dataChanged = substr($dataChanged, 0, -2);
                    $auditLog = array(
                        'description' => 'Rolul [' . $roleModel->nume . '] modificat cu succes.',
                        'old_value' => $dataOld,
                        'new_value' => $dataChanged,
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'success';
                    $result['description'] = 'Rolul [' . $roleModel->nume . '] modificat cu succes.';
                } else {
                    // add a audit log
                    $auditLog = array(
                        'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                        'new_value' => '401 /rol/editare',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'fail';
                    return response()->json($result, 401);
                }
            } else {
                $result['message'] = 'fail';
                $result['description'] = 'Rol inexistent.';
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Create role.
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
            if(Auth::user()->hasPermission('add_roles')){
                // validate data
                $this->validate($request, [
                    'name' => 'required'
                ]);

                // create role
                $collection = Role::create($request->all());
                $collection->permisiuni()->sync($request->input('pivot'));
                $result['message'] = 'success';
                $result['description'] = 'Rolul [' . $collection['name'] .'] creat.';
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /rol/adaugare',
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
     * Delete role - soft.
     * Delete our Data Type BREA(D)
     *
     * @param integer $id - Role ID
     * @return array JSON
     */
    public function delete($id)
    {
        $result = array();

        try {
            if(Auth::user()->hasPermission('delete_roles')) {
                // soft delete role
                $roleModel = Role::find($id);
                $count = Role::destroy($id);

                if($count === 1)
                {
                    // detach all pivot permissions
                    $roleModel->permisiuni()->detach();

                    $auditLog = array(
                        'description' =>  'Rolul [' . $roleModel->name . '] sters cu succes.',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);

                    $result['message'] = 'success';
                    $result['description'] = 'Rolul [' . $roleModel->name . '] sters cu succes.';
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Rolul nu poate fi sters. Probabil nu exista.';
                }
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /rol/stergere',
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
