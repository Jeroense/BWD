<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomVariant extends Model
{
    protected $table = 'customvariants';

    public function variants() {
        return $this->belongsTo('App\Variant');
    }

    protected $fillable = [
        'variantId',
        'smakeVariantId',
        'variantName',
        'ean',
        'size',
        'price',
        'tax',
        'taxRate',
        'total',
        'fileName',
        'baseColor',
        'compositeMediaId',
        'smakeCompositeMediaId',
        'productionMediaId',
        'smakeProductionMediaId',
        'isInBolCatalog',
        'width_mm',
        'height_mm',
        'salePrice',

        'boldeliverycode',
        'boldescription'
    ];
}
