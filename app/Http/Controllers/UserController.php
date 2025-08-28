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

    public function show(User $user)
    {
        Gate::authorize('view',auth()->user(), $user);
        return view('users.show', compact('user'));
    }
}
