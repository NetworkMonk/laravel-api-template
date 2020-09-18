<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Util\UUID;
use Illuminate\Support\Facades\Hash;

class Create extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check supplied data
        $request->validate([
            'username' => 'required|max:250',
            'firstName' => 'required|max:250',
            'lastName' => 'required|max:250',
            'password' => 'required|min:8|max:250',
        ]);

        // Check if a user account exists with the specified username, custom error message
        if (User::where('username', '=', $request->input('username'))->first())
        {
            abort(400, 'Username specified already exists');
        }

        // Create user account
        $user = User::create([
            'uuid' => UUID::generate(),
            'username' => $request->input('username'),
            'first_name' => $request->input('firstName'),
            'last_name' => $request->input('lastName'),
            'password' => Hash::make($request->input('password')),
            'roles' => '',
            'enabled' => true,
        ]);

        return response()->json([
            'uuid' => $user->uuid,
        ]);
    }
}
