<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $fillable = [
        'organizationName',
        'street',
        'houseNr',
        'postalcode',
        'city',
        'email',
        'phone',
        'cocNr',
        'vatNr',
        'appSerNr',
        'systemKey',
        'apiKeyBol',
        'apiKeySmake',
        'logo_id'
    ];
}
