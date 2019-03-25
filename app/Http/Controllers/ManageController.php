<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManageController extends Controller
{
    public function index() {
        return redirect()->route('manage.dashboard');
    }

    public function dashboard() {
        return view('manage.dashboard');
    }

    public function backup() {
        return 'backup here';
    }

    public function restore() {
        return 'restore here';
    }

}
