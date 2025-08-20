<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;


class UserController extends Controller
{
    public function index()
    {  
    //    Gate::authorize('viewAny', User::class);      
        return view('users.index');
    }

    public function create()
    {
        Gate::authorize('viewAny', User::class); 
        return view('users.create');
    }

    public function edit(User $user)
    {
       // dd($user);
        Gate::authorize('update', $user);
        return view('users.create',compact('user'));
    }
}
