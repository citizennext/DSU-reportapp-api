<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nume', 'prenume', 'email', 'telefon_s', 'telefon_p', 'adresa', 'cod_postal', 'parola',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'parola', 'remember_token', 'api_key'
    ];

    /**
     * Get the unit that user belongs to.
     */
    public function unitate()
    {
        return $this->belongsTo(Unitate::class);
    }
}
