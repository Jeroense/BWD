<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    public $timestamps = false;

    public function variants() {
        return $this->belongsTo('App\Variant');
    }

    protected $fillable = [
        'variantId',
        'key',
        'value',
    ];
}
