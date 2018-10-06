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

    protected $fillable = [
        'viewId',
        'compositeMediaId'
    ];
}
