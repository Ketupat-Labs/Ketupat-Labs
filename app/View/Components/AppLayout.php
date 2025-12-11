<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Auth;

class AppLayout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $currentUser = $this->getCurrentUser();
        
        return view('layouts.app', [
            'currentUser' => $currentUser,
        ])->with('currentUser', $currentUser);
    }

    /**
     * Get the current authenticated user.
     */
    protected function getCurrentUser()
    {
        if (session('user_id')) {
            return \App\Models\User::find(session('user_id'));
        }
        return Auth::user();
    }
}
