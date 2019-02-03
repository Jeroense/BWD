<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use app\Test;

class RobotController extends Controller
{
    public function manageBolOrders() {
        $bolOrders[] = $this->getBolOrders();

    }

    public function getBolorders() {
       // get BolApi list of new orders
       // return array
    }




    public function manageSmakeOrders() {
        //
    }



    public function changeName() {
        \DB::table('tests')->where('id', 1)->update(['String' => str_random(10)]);
        return null;
    }
}
