<?php

namespace App\Http\Controllers;

use App\Localitate;

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

}
