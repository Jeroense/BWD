<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BackCustomization extends Model
{
    public $timestamps = false;

    public function back() {
        return $this->belongsTo('App\Back');
    }

    protected $fillable = [
        'backId',
        'key',
        'value'
    ];
}
