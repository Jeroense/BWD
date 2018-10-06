<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Right extends Model
{
    public $timestamps = false;
    protected $table = 'right';

    public function views() {
        return $this->belongsTo('App\View');
    }

    public function rightCustomizations() {
        return $this->hasMany('App\RightCustomization');
    }

    protected $fillable = [
        'viewId',
        'compositeMediaId'
    ];
}
