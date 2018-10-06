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

    public function views() {
        return $this->hasMany('App\View');
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
