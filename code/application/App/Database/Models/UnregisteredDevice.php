<?php
namespace App\Database\Models;

use Illuminate\Database\Eloquent\Model;


class UnregisteredDevice extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [

        'id', 'ext-ip', 'int-ip','deviceid'

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $hidden = [

        'id',

    ];

    /*
    * Get Todo of User
    *


    public function todo()

    {
        return $this->hasMany('Todo');

    }
    */
}
