<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        //$users = User::with('roles','createdBy')->get();
        return view('users.index');

    }

    public function create()
    {
        return view('users.create');
    }
}
