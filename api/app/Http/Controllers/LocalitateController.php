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
     * Get individual record Localitate, by ID
     *
     * @param integer $id - Localitate ID
     * @return array JSON
     */
    public function find($id)
    {
        $collection = Localitate::find($id);

        return response()->json($collection);
    }

    /**
     * Get individual record Localitate, by Slug
     *
     * @param string $slug - Localitate slug
     * @return array JSON
     */
    public function findBySlug($slug)
    {
        $collection = Localitate::where('slug', $slug)->first();

        return response()->json($collection);
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
        $modelJudet = Judet::select('id')->where(['slug' => $slug, 'deleted_at' => null])->first();
        $collection = Localitate::where(['judet_id' => $modelJudet->id, 'deleted_at' => null])->get();

        return response()->json($collection);
    }
}
