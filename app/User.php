<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class User extends Model
{
    //
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $attributes = [
        'roles' => '',
    ];

    protected $fillable = [
        'uuid',
        'username',
        'first_name',
        'last_name',
        'password',
        'roles',
        'enabled',
    ];

    protected $hidden = [
        'password',
    ];

    public function attributes()
    {
        return $this->hasMany('App\Attribute', 'user', 'uuid');
    }

    public function sessions()
    {
        return $this->hasMany('App\Sessions', 'user', 'uuid');
    }

    public function setPassword($password)
    {
        $this->password = Hash::make($password);
    }

    public function verifyPassword($password) : bool
    {
        if (Hash::check($password, $this->password))
        {
            return true;
        }
        return false;
    }

    public function addRole(String $role)
    {
        if ($this->testRole($role))
        {
            return;
        }
        $this->roles = $this->roles === '' ? $role : ($this->roles . ',' . $role);
    }

    public function removeRole(String $role)
    {
        if ($this->roles === '')
        {
            return;
        }

        $roles = \explode(',', $this->roles);
        $index = \array_search($role, $roles);
        if ($index === false)
        {
            return;
        }
        \array_splice($roles, $index, 1);
        $this->roles = implode(',', $roles);
    }

    public function testRole(String $role) : bool
    {
        return \array_search($role, explode(',', $this->roles)) === false ? false : true;
    }
}
