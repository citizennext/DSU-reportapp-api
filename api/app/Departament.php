<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Departament extends Model
{
    use SoftDeletes;

    // set custom table name
    protected $table = 'departamente';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the unitati that is owened by the departament.
     */
    public function unitati() {
        return $this -> hasMany(Unitate::class);
    }
}
