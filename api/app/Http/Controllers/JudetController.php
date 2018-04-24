<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Auth;

use App\Localitate;
use App\Judet;
use App\Audit;

class JudetController extends Controller
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
     * Get all active judete
     * Browse our Data Type (B)READ
     *
     * @return array JSON
     */
    public function index()
    {
        if(Auth::user()->hasPermission('browse_judete')){
            $collection = Judet::where('deleted_at', null)->get();
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Get individual record Judet, by ID
     * Read our Data Type B(R)EAD
     *
     * @param integer $id - Judet ID
     * @return array JSON
     */
    public function find($id)
    {
        if(Auth::user()->hasPermission('read_judete')){
            $collection = Judet::find($id);
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Get individual record Judet, by Slug
     * Read our Data Type B(R)EAD
     *
     * @param string $slug - Judet slug
     * @return array JSON
     */
    public function findBySlug($slug)
    {
        if(Auth::user()->hasPermission('read_judete')){
            $collection = Judet::where('slug', $slug)->first();
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    /**
     * Create judet.
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
            if(Auth::user()->hasPermission('add_judete')){
                // validate data
                $this->validate($request, [
                    'nume' => 'required',
                ]);
                // check if exist a judet with similar slug
                $slug = str_slug($request->input('nume'));
                if(Judet::where('slug', '=', $slug)->count() > 0) {
                    do {
                        $slug = $slug . '-' . rand(0, 99);
                    } while (Judet::where('slug', '=', $slug)->count() > 0);
                }
                // insert slug in the request array
                $request->request->add(['slug' => $slug]);

                // create judet
                $collection = Judet::create($request->all());
                $result['message'] = 'success';
                $result['description'] = 'Judetul [' . $collection['nume'] .' -> '. $collection['slug'] .'] creat.';
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /judete/adaugare',
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
     * Edit individual judet.
     * Edit our Data Type BR(E)AD
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
    public function edit(Request $request)
    {
        $result = array();

        try{
            if($judetModel = Judet::find($request->input('request_id'))){
                if(Auth::user()->hasPermission('edit_judete')){
                    $requestOld = $judetModel->toArray();
                    $requestData = $request->all();
                    unset($requestData['id'], $requestData['request_id'], $requestData['_url']);
                    // set new slug
                    $slug = str_slug($requestData['nume']);
                    if(Judet::where('slug', '=', $slug)->count() > 0) {
                        do {
                            $slug = $slug . '-' . rand(0, 99);
                        } while (Judet::where('slug', '=', $slug)->count() > 0);
                    }
                    // insert slug in the request array
                    $requestData['slug'] = $slug;

                    $judetModel->update($requestData);
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
                        'description' => 'Judetul [' . $judetModel->nume . ' -> ' . $judetModel->slug . '] modificat cu succes.',
                        'old_value' => $dataOld,
                        'new_value' => $dataChanged,
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'success';
                    $result['description'] = 'Judet [' . $judetModel->nume . ' -> ' . $judetModel->slug . '] modificat cu succes.';
                } else {
                    // add a audit log
                    $auditLog = array(
                        'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                        'new_value' => '401 /judete/editare',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);
                    $result['message'] = 'fail';
                    return response()->json($result, 401);
                }
            } else {
                $result['message'] = 'fail';
                $result['description'] = 'Judet inexistenta.';
            }
        } catch (QueryException $exception) {
            $result['message'] = 'fail';
            $result['description'] = 'DB Exception #' . $exception->errorInfo[1] . '[' .$exception->errorInfo[2] . ']';
        }

        return response()->json($result);
    }

    /**
     * Delete localitate.
     * Delete our Data Type BREA(D)
     *
     * @param integer $id - Judet ID
     * @return array JSON
     */
    public function delete($id)
    {
        $result = array();

        try {
            if(Auth::user()->hasPermission('delete_judete')) {
                // soft delete judet
                $judetModel = Judet::find($id);
                $count = Judet::destroy($id);

                if($count === 1)
                {
                    // soft delete all localitati records
                    Localitate::where('judet_id', '=', $id)->update(['deleted_at' => Carbon::now()]);

                    $auditLog = array(
                        'description' =>  'Judetul [' . $judetModel->nume . ' -> ' . $judetModel->slug . '] sters cu succes.',
                        'user_id' => Auth::user()->id
                    );
                    Audit::create($auditLog);

                    $result['message'] = 'success';
                    $result['description'] = 'Judetul [' . $judetModel->nume . ' -> ' . $judetModel->slug . '] sters cu succes.';
                } else {
                    $result['message'] = 'fail';
                    $result['description'] = 'Judetul nu poate fi sters. Probabil nu exista.';
                }
            } else {
                // add a audit log
                $auditLog = array(
                    'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                    'new_value' => '401 /judete/stergere',
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
