<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    public $timestamps = false;

    public function variants() {
        return $this->belongsTo('App\Variant');
    }

    public function front() {
        return $this->hasMany('App\Front');
    }

    public function back() {
        return $this->hasMany('App\Back');
    }

    public function left() {
        return $this->hasMany('App\Left');
    }

    public function right() {
        return $this->hasMany('App\Right');
    }

    protected $fillable = [
        'variantId',
    ];
}
