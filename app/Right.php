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

    protected $fillable = [
        'viewId',
        'compositeMediaId'
    ];
}
