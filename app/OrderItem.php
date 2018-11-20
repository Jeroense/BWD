<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'orderItems';
    public $timestamps = false;

    public function orders() {
        return $this->belongsTo('App\Order');
    }
}
