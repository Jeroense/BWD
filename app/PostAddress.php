<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostAddress extends Model
{
    public function customers() {
        return $this->belongsTo('App\Customer');
    }

    protected $fillable = [
        'customerId',
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
    ];
}
