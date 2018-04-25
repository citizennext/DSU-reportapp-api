<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Auth;

use App\Permission;
use App\Audit;

class PermissionController extends Controller
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
     * Get all active permissions
     * Browse our Data Type (B)READ
     *
     * @return array JSON
     */
    public function index()
    {
        if(Auth::user()->hasPermission('browse_permissions')){
            $collection = Permission::with('roluri')->where('deleted_at', null)->get();
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Get individual record Permission, by ID
     * Read our Data Type B(R)EAD
     *
     * @param integer $id - Permission ID
     * @return array JSON
     */
    public function find($id)
    {
        if(Auth::user()->hasPermission('read_permissions')){
            $collection = Permission::with('roluri')->find($id);
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Edit individual Permission.
     * Edit our Data Type BR(E)AD
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
    public function edit(Request $request)
    {
        $result = array();

        try{
            if($permissionModel = Permission::find($request->input('request_id'))){
                if(Auth::user()->hasPermission('edit_permissions')){
                    $requestOld = $permissionModel->toArray();
                    $requestData = $request->all();
                    unset($requestData['id'], $requestData['request_id'], $requestData['_url']);

                    $permissionModel->update($requestData);
                    $permissionModel->roluri()->sync($request->input('pivot'));
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
                        'description' => 'Permisiunea [' . $permissionModel->key . '] modificata cu succes.',
                        'old_value' => $dataOld,
                        'new_value' => $dataChanged,
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'success';
                    $result['description'] = 'Permisiunea [' . $permissionModel->key . '] modificata cu succes.';
                } else {
                    // add a audit log
                    $auditLog = array(
                        'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                        'new_value' => '401 /permisiune/editare',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'fail';
                    return response()->json($result, 401);
                }
            } else {
                $result['message'] = 'fail';
                $result['description'] = 'Permisiune inexistenta.';
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Create permission.
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
            if(Auth::user()->hasPermission('add_permissions')){
                // validate data
                $this->validate($request, [
                    'key' => 'required'
                ]);

                // create permission
                $collection = Permission::create($request->all());
                $collection->roluri()->sync($request->input('pivot'));
                $result['message'] = 'success';
                $result['description'] = 'Permisiunea [' . $collection['key'] .'] creata.';
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /permisiune/adaugare',
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
     * Delete permission - soft.
     * Delete our Data Type BREA(D)
     *
     * @param integer $id - Permission ID
     * @return array JSON
     */
    public function delete($id)
    {
        $result = array();

        try {
            if(Auth::user()->hasPermission('delete_permissions')) {
                // soft delete permission
                $permissionModel = Permission::find($id);
                $count = Permission::destroy($id);

                if($count === 1)
                {
                    // detach all pivot roles
                    $permissionModel->roluri()->detach();

                    $auditLog = array(
                        'description' =>  'Permisiunea [' . $permissionModel->key . '] stearsa cu succes.',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);

                    $result['message'] = 'success';
                    $result['description'] = 'Permisiunea [' . $permissionModel->key . '] stearsa cu succes.';
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Permisiunea nu poate fi stearsa. Probabil nu exista.';
                }
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /permisiune/stergere',
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
