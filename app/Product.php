<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function variants() {
        {
            return $this->hasMany('App\Variant');
        }
    }

    public function attributes() {
        {
            return $this->hasMany('App\ProductAttribute');
        }
    }

    public function attrValues() {
        {
            return $this->hasOneThrough(
                'App\AttributeValue',
                'App\ProductAttribute',
                'product_id',
                'attribute_id',
                'id',
                'id'
            );
        }
    }

    protected $fillable = [
        'smakeId', 'title', 'description',
    ];
}
