<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function Items() {
        {
            return $this->hasMany('App\Items');
        }
    }
}
