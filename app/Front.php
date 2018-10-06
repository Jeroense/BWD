<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Front extends Model
{
    public $timestamps = false;
    protected $table = 'front';

    public function views() {
        return $this->belongsTo('App\View');
    }

    public function frontCustomizations() {
        return $this->hasMany('App\FrontCustomization');
    }

    protected $fillable = [
        'viewId',
        'compositeMediaId'
    ];
}
