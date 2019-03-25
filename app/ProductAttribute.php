<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    public function products() {
        return $this->belongsTo('App\Product');
    }

    public function attrValues() {
        {
            return $this->hasOne('App\AttributeValue', 'attribute_id');
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_attribute_key'
    ];


}
