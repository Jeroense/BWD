<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BolProduktieOffer extends Model
{
    protected $fillable = [

        'offerId',
        'ean',
        'referenceCode',
        'onHoldByRetailer',
        'unknownProductTitle',
        'bundlePricesQuantity',
        'bundlePricesPrice',
        'stockAmount',
        'correctedStock',
        'stockManagedByRetailer',
        'fulfilmentType',
        'fulfilmentDeliveryCode',
        'fulfilmentConditionName',
        'fulfilmentConditionCategory',
        'notPublishableReasonsCode',
        'notPublishableReasonsDescription'
    ];
}


// from BOL produktie server:   GET  offers/38dff9a2-dc45-4201-85f2-cb0ae0cd80d5
// Mon, 29 Apr 2019 17:29:29
// 1556551769  (unix time request)
// 28
// 27
// 1556551770   (unix time x-limit-reset)

// 200 OK

// {"offerId":"38dff9a2-dc45-4201-85f2-cb0ae0cd80d5",
//  "ean":"7435156898868",
//  "referenceCode":"test123",
//  "onHoldByRetailer":true,
//  "unknownProductTitle":"pacman and friend",
//  "pricing":
// 	{"bundlePrices":
// 		[
// 			{"quantity":1,"price":40.00}
// 		]
// 	},
//  "stock":{
// 	"amount":100,
// 	"correctedStock":0,
// 	"managedByRetailer":true
// 	 },

//  "fulfilment":{
// 	"type":"FBR",
// 	"deliveryCode":"3-5d"},
// 	"store":{},
// 	"condition":{
// 			"name":"NEW",
// 			"category":"NEW"
// 		    },
//  "notPublishableReasons":[
// 				{"code":"4004",
// 				 "description":"Unknown EAN code 7435156898868."}
// 			 ]
// }

