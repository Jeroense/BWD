<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomVariant extends Model
{
    protected $table = 'customvariants';

    public function variants() {
        return $this->belongsTo('App\Variant');
    }

    protected $fillable = [
        'ean',
        'size',
        'color',
        'isPublishedAtBol'
    ];
}
