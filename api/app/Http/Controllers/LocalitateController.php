<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Auth;

use App\Localitate;
use App\Judet;
use App\Audit;

class LocalitateController extends Controller
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
     * Get all active localitati, including judet
     * Browse our Data Type (B)READ
     *
     * @return array JSON
     */
  public function index()
  {
    if (Auth::user()->hasPermission('browse_localitati')) {
        $collection = Localitate::with(['judet' => function ($query) {
          $query->where('deleted_at', null);
        }])->where('deleted_at', null)->get();
        return response()->json($collection);
    } else {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

    /**
     * Get individual record Localitate, by ID
     * Read our Data Type B(R)EAD
     *
     * @param integer $id - Localitate ID
     * @return array JSON
     */
  public function find($id)
  {
    if (Auth::user()->hasPermission('read_localitati')) {
        $collection = Localitate::find($id);
        return response()->json($collection);
    } else {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

    /**
     * Get individual record Localitate, by Slug
     * Read our Data Type B(R)EAD
     *
     * @param string $slug - Localitate slug
     * @return array JSON
     */
  public function findBySlug($slug)
  {
    if (Auth::user()->hasPermission('read_localitati')) {
        $collection = Localitate::where('slug', $slug)->first();
        return response()->json($collection);
    } else {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

    /**
     * Get all active localitati related to one judet, by judet slug
     * Browse our Data Type (B)READ
     *
     * @param string $slug - Judet slug
     * @return array JSON
     */
  public function localitatiByJudet($slug)
  {
    if (Auth::user()->hasPermission('browse_localitati')) {
        $modelJudet = Judet::select('id')->where(['slug' => $slug, 'deleted_at' => null])->first();
        $collection = Localitate::where(['judet_id' => $modelJudet->id, 'deleted_at' => null])->get();
        return response()->json($collection);
    } else {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

    /**
     * Create localitate.
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
      if (Auth::user()->hasPermission('add_localitati')) {
        // validate data
        $this->validate($request, [
            'nume' => 'required',
            'judet_id' => 'required',
        ]);
        // check if exist a localitate with similar slug
        $slug = str_slug($request->input('nume'));
        if (Localitate::where('slug', '=', $slug)->count() > 0) {
          do {
              $slug = $slug . '-' . rand(0, 99);
          } while (Localitate::where('slug', '=', $slug)->count() > 0);
        }
        // insert slug in the request array
        $request->request->add(['slug' => $slug]);

        // create localitate
        $collection = Localitate::create($request->all());
        $result['message'] = 'success';
        $result['description'] = 'Localitatea [' . $collection['nume'] .'] creata.';
      } else {
          // add a audit log
          $auditLog = array(
              'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
              'new_value' => '401 /localitate/adaugare',
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
     * Edit individual localitate.
     * Edit our Data Type BR(E)AD
     *
     * @param Request $request - data sent by form | by http request
     * @return array JSON
     */
  public function edit(Request $request)
  {
      $result = array();

    try {
      if ($localitateModel = Localitate::find($request->input('request_id'))) {
        if (Auth::user()->hasPermission('edit_localitati')) {
            $requestOld = $localitateModel->toArray();
            $requestData = $request->all();
            unset($requestData['id'], $requestData['request_id'], $requestData['_url']);
            // set new slug
            $slug = str_slug($requestData['nume']);
          if (Localitate::where('slug', '=', $slug)->count() > 0) {
            do {
                  $slug = $slug . '-' . rand(0, 99);
            } while (Localitate::where('slug', '=', $slug)->count() > 0);
          }
            // insert slug in the request array
            $requestData['slug'] = $slug;

            $localitateModel->update($requestData);
            // add a audit log
            $dataOld = '';
            $dataChanged = '';
          foreach ($requestData as $key => $value) {
                $dataOld .= $key . ' = ' . $requestOld[$key] . ', ';
                  $dataChanged .= $key . ' = ' . $value . ', ';
          }
              $dataOld = substr($dataOld, 0, -2);
              $dataChanged = substr($dataChanged, 0, -2);
              $auditLog = array(
                  'description' => 'Localitatea [' . $localitateModel->nume . '] modificata cu succes.',
                  'old_value' => $dataOld,
                  'new_value' => $dataChanged,
                  'user_id' => Auth::user()->id
              );
              Audit::create($auditLog);
              $result['message'] = 'success';
              $result['description'] = 'Localitatea [' . $localitateModel->nume . '] modificata cu succes.';
        } else {
            // add a audit log
            $auditLog = array(
                'description' => 'Accesare neautorizata ' . (strlen(Auth::user()->prenume) > 0 ? Auth::user()->prenume . ' ' . Auth::user()->nume : Auth::user()->nume),
                'new_value' => '401 /localitati/editare',
                'user_id' => Auth::user()->id
            );
            Audit::create($auditLog);
            $result['message'] = 'fail';
            return response()->json($result, 401);
        }
      } else {
          $result['message'] = 'fail';
          $result['description'] = 'Localitate inexistenta.';
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
     * @param integer $id - Localitate ID
     * @return array JSON
     */
  public function delete($id)
  {
      $result = array();

    try {
      if (Auth::user()->hasPermission('delete_localitati')) {
        // soft delete localitate
        $localitateModel = Localitate::find($id);
        $count = Localitate::destroy($id);

        if ($count === 1) {
            $auditLog = array(
                'description' => 'Localitatea [' . $localitateModel->nume . ' -> ' . $localitateModel->slug . '] stearsa cu succes.',
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
