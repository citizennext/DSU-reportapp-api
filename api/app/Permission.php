<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;

    // set custom table name
    protected $table = 'permissions';

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
        'key', 'table_name', 'permission_group_id',
    ];

    /**
     * Get the permissions that are owned by the role.
     */
    public function roluri() {
        return $this -> belongsToMany(Role::class);
    }

}
