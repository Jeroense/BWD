<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FrontCustomization extends Model
{
    protected $table = 'frontCustomizations';
    public $timestamps = false;

    public function front() {
        return $this->belongsTo('App\Front');
    }

    protected $fillable = [
        'backId',
        'key',
        'value'
    ];
}
