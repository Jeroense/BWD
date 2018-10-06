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

    protected $fillable = [
        'viewId',
        'compositeMediaId'
    ];
}
