<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unitate extends Model
{
    use SoftDeletes;

    // set custom table name
  protected $table = 'unitati';

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
        'nume', 'descriere', 'adresa', 'cod_postal', 'telefon', 'localitate', 'judet', 'parent_id', 'departament_id',
    ];

    /**
     * Get the user that owns the unit.
     */
  public function user() {
      return $this -> hasOne(User::class);
  }

    /**
     * Get the parent unit that owns the unit.
     */
  public function parent()
  {
      return $this->belongsTo(Unitate::class, 'parent_id');
  }

    /**
     * Get the children units that owned by the unit.
     */
  public function children()
  {
      return $this->hasMany(Unitate::class, 'parent_id');
  }

    /**
     * Get the departament  that owns the unit.
     */
  public function departament()
  {
      return $this->belongsTo(Departament::class, 'departament_id');
  }
}
