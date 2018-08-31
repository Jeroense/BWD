<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function login() {
        if(Auth::guest()) {
                return view('auth.login');
        }
        return view('home');
    }
}
