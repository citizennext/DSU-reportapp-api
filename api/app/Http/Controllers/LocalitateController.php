<?php

namespace App\Http\Controllers;

use App\Localitate;
use App\Judet;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Auth;

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
        if(Auth::user()->hasPermission('browse_localitati')){
            $collection = Localitate::with(['judet' => function($query) { $query->where('deleted_at',null); }])->where('deleted_at', null)->get();
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
        if(Auth::user()->hasPermission('read_localitati')){
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
        if(Auth::user()->hasPermission('read_localitati')){
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
        if(Auth::user()->hasPermission('browse_localitati')){
            $modelJudet = Judet::select('id')->where(['slug' => $slug, 'deleted_at' => null])->first();
            $collection = Localitate::where(['judet_id' => $modelJudet->id, 'deleted_at' => null])->get();
            return response()->json($collection);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
