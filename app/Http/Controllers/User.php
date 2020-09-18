<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class User extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Show all users, we do not load attributes here
        $users = \App\User::all();
        return response()->json($users);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $username
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $username)
    {
        $token = $request->authorizationToken;
        $globalAdmin = \App\User::find($token['sub'])->testRole('Global Administrator');

        // Attempt to load the user specified
        $user = \App\User::where('username', '=', $username)->first();
        if (!$user)
        {
            abort(404, 'User not found');
        }
        $currentUser = ($token['sub'] === $user->uuid) ? true : false;

        // Build the result
        $result = [
            'uuid' => $user->uuid,
            'username' => $user->username,
        ];

        // If we are not a global admin and this is not the current user, return now
        if (!$currentUser && !$globalAdmin)
        {
            return $result;
        }

        // Add additional fields to result
        $result['firstName'] = $user->first_name;
        $result['lastName'] = $user->last_name;
        $result['createdAt'] = $user->created_at;
        $result['updatedAt'] = $user->updated_at;

        // Only include roles if we are a global admin or this is the current user
        if ($globalAdmin || $currentUser)
        {
            $result['roles'] = $user->roles;
        }

        // Load user attributes, do not output hidden attributes
        $result['attributes'] = [];
        $attributes = $user->attributes;
        foreach ($attributes as $attribute)
        {
            if ($attribute->hidden == FALSE)
            {
                $result['attributes'][$attribute->name] = $attribute['value'];
            }
        }

        // Return user data
        return response()->json($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $username
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $username)
    {
        $token = $request->authorizationToken;
        $globalAdmin = \App\User::find($token['sub'])->testRole('Global Administrator');

        // Attempt to load the user specified
        $user = \App\User::where('username', '=', $username)->first();
        if (!$user)
        {
            abort(404, 'User not found');
        }
        $currentUser = ($token['sub'] === $user->uuid) ? true : false;

        // If we are not the current user or a global admin exit now
        if (!$currentUser && !$globalAdmin)
        {
            abort(403, 'Access Denied');
        }

        // Check supplied data
        $request->validate([
            'firstName' => 'max:250',
            'lastName' => 'max:250',
            'password' => 'min:8|max:250',
        ]);

        $user->first_name = $request->input('firstName', $user->first_name);
        $user->last_name = $request->input('lastName', $user->last_name);
        if ($request->input('password') !== NULL)
        {
            $user->setPassword($request->input('password'));
        }

        if ($globalAdmin)
        {
            $user->roles = $request->input('roles', $user->roles);
            $user->enabled = $request->input('enabled', $user->enabled);
        }

        foreach($request->get('attributes', []) as $name => $value)
        {
            $attribute = $user->attributes()->firstOrNew(['name' => $name]);
            $attribute->value = isset($value['value']) ? $value['value'] : '';
            $attribute->hidden = isset($value['hidden']) ? $value['hidden'] : FALSE;
            $attribute->save();
        }

        $user->save();

        return response()->json(['status' => 'success']);
    }
}
