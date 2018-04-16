<?php

namespace App\Http\Controllers;

use App\Localitate;
use App\Judet;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class LocalitateController extends Controller
{
    /**
     * Get all active localitati, including judet
     * Browse our Data Type (B)READ
     *
     * @return array JSON
     */
    public function index()
    {
        $collection = Localitate::with(['judet' => function($query) { $query->where('deleted_at',null); }])->where('deleted_at', null)->get();

        return response()->json($collection);
    }

    /**
     * Get all active localitati related to one judet, by judet slug
     * Browse our Data Type (B)READ
     *
     * @return array JSON
     */
    public function localitatiByJudet(Request $request)
    {
        $modelJudet = Judet::select('id')->where(['slug' => $request->input('slug'), 'deleted_at' => null])->first();
        $collection = Localitate::where(['judet_id' => $modelJudet->id, 'deleted_at' => null])->get();

        return response()->json($collection);
    }
}
