<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    // set custom table name
  protected $table = 'roles';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
  protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
  protected $fillable = [
        'name', 'display_name',
    ];

    /**
     * Get the user that owns the role.
     */
  public function user() {
      return $this -> hasOne(User::class);
  }

    /**
     * Get the permissions that are owned by the role.
     */
  public function permisiuni() {
      return $this -> belongsToMany(Permission::class);
  }
}
