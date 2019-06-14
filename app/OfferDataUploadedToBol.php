<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfferDataUploadedToBol extends Model
{

    protected $table = 'offer_data_uploaded_to_bol';

    protected $fillable = [
        'process_status_id',
        'eventType',
        'offerId',
        'ean',
        'productTitle',
        'deliveryCode',
        'stock',
        'stockManagedByRetailer',
        'price',
        'quantityPrice',
        'onHoldByRetailer',
        'fulfilment',
        'condition',
        'status',
        'refcode'
    ];



}

