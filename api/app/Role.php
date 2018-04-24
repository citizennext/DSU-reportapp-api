<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    /**
     * Get the user that owns the role.
     */
    public function user() {
        return $this -> hasOne(User::class);
    }
}
