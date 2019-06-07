<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'smakeOrderId',
        'customerId',
        'bolOrderNr',
        'orderStatus',
        'deliveryDate',
        'shippingMethod',
        'shippingRate',
        'orderAmount',
        'boltransactionFee',
        'totalTax'

    ];

    public function orderItems() {
        {
            return $this->hasMany('App\OrderItem', 'orderId');
        }
    }
}
