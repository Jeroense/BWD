<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function variants() {
        {
            return $this->hasMany('App\Variant');
        }
    }

    protected $fillable = [
        'smakeId', 'title', 'description',
    ];
}
