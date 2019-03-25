<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    public function ProductAttributes() {
        return $this->belongsTo('App\ProductAttribute');
    }
}
