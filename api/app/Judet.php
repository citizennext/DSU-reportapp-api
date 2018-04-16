<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Judet extends Model
{
    use SoftDeletes;

    // set custom table name
    protected $table = 'judete';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Get the loc that owned by the judet.
     */
    public function localitate()
    {
        return $this->hasMany(Localitate::class);
    }
}
