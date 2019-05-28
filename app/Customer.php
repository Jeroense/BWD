<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public function postAddress() {
        {
            return $this->hasOne('App\PostAddress', 'customerId');
        }
    }

    protected $fillable = [
        'salutation',
        'firstName',
        'lnPrefix',
        'lastName',
        'street',
        'houseNr',
        'houseNrPostfix',
        'postalCode',
        'city',
        'provinceCode',
        'countryCode',
        'phone',
        'email',
        'hasDeliveryAddress',
        'hasBillingAddress',
    ];
}
