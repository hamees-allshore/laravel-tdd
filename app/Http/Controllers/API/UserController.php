<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ['users' => User::all()];
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return ['user' => $user];
    }

}
