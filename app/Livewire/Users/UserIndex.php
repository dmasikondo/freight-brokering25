<?php

namespace App\Livewire\Users;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\User;
use App\Policies\UserPolicy;

use Livewire\Component;

class UserIndex extends Component
{
    #[Computed]
    public function users()
    {
        $authenticatedUser = auth()->user();
        $users = (new UserPolicy())->viewAny($authenticatedUser)->with('roles','createdBy')->get();    
        return $users;
               
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







