<?php

namespace App\Livewire\Users;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\User;

use Livewire\Component;

class UserIndex extends Component
{
    #[Computed]
    public function users()
    {
        return User::with('roles','createdby')->get();
    }

    public function userEdit($slug)
    {
        //$user = User::where('slug',$slug)->firstOrFail();
        return redirect()->route('users.edit',compact('slug'));
    }    

    public function render()
    {
        return view('livewire.users.user-index');
    }
}







