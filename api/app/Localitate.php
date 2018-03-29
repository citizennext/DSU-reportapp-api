<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Localitate extends Model
{
    use SoftDeletes;

    // set custom table name
    protected $table = 'localitati';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the parent that owns the loc.
     */
    public function judet() {
        return $this -> belongsTo(Judet::class, 'judet_id');
    }
}
