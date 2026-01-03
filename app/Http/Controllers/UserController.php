<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Policies\UserPolicy;


class UserController extends Controller
{
    public function index()
    {  
       Gate::authorize('create', auth()->user());          
        return view('users.index');
    }

    public function create()
    {
        $authenticatedUser = auth()->user();
        Gate::authorize('create', $authenticatedUser); 
        return view('users.create');
    }

    public function edit(User $user)
    {
        $slug = $user->slug;
        $authenticatedUser = auth()->user();
        Gate::authorize('create', $authenticatedUser); 
        return view('users.edit', compact('slug'));
    }    

    public function show(User $user)
    {
        Gate::authorize('view',$user);
        return view('users.show', compact('user'));
    }
}
