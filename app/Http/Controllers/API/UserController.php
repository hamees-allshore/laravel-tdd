<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoogleAlertService;

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
        return [
            'user' => $user,
            'feeds' => (new GoogleAlertService)->getAllFeeds($user->name),
        ];
    }

}
