<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function dashboard() {
        return view('orders.dashboard');
    }
}
