<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Left extends Model
{
    public $timestamps = false;
    protected $table = 'left';

    public function views() {
        return $this->belongsTo('App\View');
    }

    protected $fillable = [
        'viewId',
        'compositeMediaId'
    ];
}
