<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    public function products() {
        return $this->belongsTo('App\Product');
    }

    public function attributes() {
        return $this->hasMany('App\Attribute');
    }

    protected $fillable = [
        'variantId',
        'productId',
        'price',
        'tax',
        'taxRate',
        'mediaId',
        'isPublished',
        'ean',
    ];
}
