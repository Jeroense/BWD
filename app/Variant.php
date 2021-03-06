<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    public function products() {
        return $this->belongsTo('App\Product');
    }

    public function attributes() {
        return $this->hasMany('App\Attribute', 'variantId');
    }

    public function customvariants() {
        return $this->hasMany('App\CustomVariant', 'variantId');
    }

    // public function views() {
    //     return $this->hasMany('App\View');
    // }

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
