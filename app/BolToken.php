<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BolToken extends Model
{
    protected $fillable = [
        'access_token',
        'at_unix_time',
        'seconds_valid'
    ];
}

