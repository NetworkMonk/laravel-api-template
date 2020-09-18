<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    //
    protected $fillable = [
        'user',
        'name',
        'value',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'uuid', 'user');
    }
}
