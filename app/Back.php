<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Back extends Model
{
    public $timestamps = false;
    protected $table = 'back';

    public function views() {
        return $this->belongsTo('App\View');
    }

    public function backCustomizations() {
        return $this->hasMany('App\BackCustomization');
    }

    protected $fillable = [
        'viewId',
        'compositeMediaId'
    ];
}
