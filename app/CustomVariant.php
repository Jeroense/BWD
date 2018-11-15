<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomVariant extends Model
{
    protected $table = 'customvariants';

    protected $fillable = [
        'ean',
        'size',
        'color'
    ];
}
