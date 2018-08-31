<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // https://api.myjson.com/bins/1fmmo8
        $rawData = '{
            "data": [
                {
                    "is_test": false,
                    "id": 1,
                    "title": "qui ut nemo",
                    "description": "Autem nesciunt ipsam excepturi non distinctio quas ullam placeat. Quam quidem rerum veniam corrupti velit aperiam non. Repudiandae rem incidunt atque optio vero et voluptatem. Fuga et quos in ipsa.",
                    "created_at": "2017-11-15T08:07:48+00:00",
                    "updated_at": "2017-11-15T08:07:48+00:00",
                    "variants": [
                        {
                            "id": 90,
                            "total": 101.7,
                            "price": 85.46,
                            "tax": 16.24,
                            "tax_rate": 19,
                            "attributes": [
                                {
                                    "name": "color",
                                    "value": "RoyalBlue"
                                },
                                {
                                    "name": "size",
                                    "value": "S"
                                }
                            ],
                            "origin": {
                                "code": "0635579894134"
                            },
                            "media_id": 607,
                            "views": {
                                "back": {
                                    "composite_media_id": 609,
                                    "customizations": []
                                },
                                "left": {
                                    "composite_media_id": 610,
                                    "customizations": []
                                },
                                "front": {
                                    "composite_media_id": 608,
                                    "customizations": []
                                },
                                "right": {
                                    "composite_media_id": 611,
                                    "customizations": []
                                }
                            },
                            "created_at": "2017-11-15T08:07:48+00:00",
                            "updated_at": "2017-11-15T08:07:50+00:00"
                        },
                        {
                            "id": 92,
                            "total": 185.21,
                            "price": 173.09,
                            "tax": 12.12,
                            "tax_rate": 7,
                            "attributes": [
                                {
                                    "name": "color",
                                    "value": "DarkCyan"
                                },
                                {
                                    "name": "size",
                                    "value": "L"
                                }
                            ],
                            "origin": {
                                "code": "6904687103174"
                            },
                            "media_id": 617,
                            "views": {
                                "back": {
                                    "composite_media_id": 619,
                                    "customizations": []
                                },
                                "left": {
                                    "composite_media_id": 620,
                                    "customizations": []
                                },
                                "front": {
                                    "composite_media_id": 618,
                                    "customizations": []
                                },
                                "right": {
                                    "composite_media_id": 621,
                                    "customizations": []
                                }
                            },
                            "created_at": "2017-11-15T08:07:49+00:00",
                            "updated_at": "2017-11-15T08:07:50+00:00"
                        },
                        {
                            "id": 94,
                            "total": 24.52,
                            "price": 20.61,
                            "tax": 3.91,
                            "tax_rate": 19,
                            "attributes": [
                                {
                                    "name": "color",
                                    "value": "Blue"
                                },
                                {
                                    "name": "size",
                                    "value": "L"
                                }
                            ],
                            "origin": {
                                "code": "1243574262255"
                            },
                            "media_id": 627,
                            "views": {
                                "back": {
                                    "composite_media_id": 629,
                                    "customizations": []
                                },
                                "left": {
                                    "composite_media_id": 630,
                                    "customizations": []
                                },
                                "front": {
                                    "composite_media_id": 628,
                                    "customizations": []
                                },
                                "right": {
                                    "composite_media_id": 631,
                                    "customizations": []
                                }
                            },
                            "created_at": "2017-11-15T08:07:50+00:00",
                            "updated_at": "2017-11-15T08:07:50+00:00"
                        }
                    ]
                },
                {
                    "is_test": false,
                    "id": 30,
                    "title": "ut",
                    "description": "Et voluptatem quas neque praesentium qui harum. Ea occaecati perferendis inventore tempore nemo. Nobis perferendis voluptas sed tempore at.",
                    "created_at": "2017-11-15T08:07:51+00:00",
                    "updated_at": "2017-11-15T08:07:51+00:00",
                    "variants": [
                        {
                            "id": 96,
                            "total": 168.98,
                            "price": 142,
                            "tax": 26.98,
                            "tax_rate": 19,
                            "attributes": [
                                {
                                    "name": "color",
                                    "value": "DarkRed"
                                },
                                {
                                    "name": "size",
                                    "value": "M"
                                }
                            ],
                            "origin": {
                                "code": "9410671772396"
                            },
                            "media_id": 642,
                            "views": {
                                "back": {
                                    "composite_media_id": 644,
                                    "customizations": []
                                },
                                "left": {
                                    "composite_media_id": 645,
                                    "customizations": []
                                },
                                "front": {
                                    "composite_media_id": 643,
                                    "customizations": []
                                },
                                "right": {
                                    "composite_media_id": 646,
                                    "customizations": []
                                }
                            },
                            "created_at": "2017-11-15T08:07:51+00:00",
                            "updated_at": "2017-11-15T08:07:55+00:00"
                        },
                        {
                            "id": 98,
                            "total": 95.92,
                            "price": 80.61,
                            "tax": 15.31,
                            "tax_rate": 19,
                            "attributes": [
                                {
                                    "name": "color",
                                    "value": "BlanchedAlmond"
                                },
                                {
                                    "name": "size",
                                    "value": "L"
                                }
                            ],
                            "origin": {
                                "code": "0473312504175"
                            },
                            "media_id": 652,
                            "views": {
                                "back": {
                                    "composite_media_id": 654,
                                    "customizations": []
                                },
                                "left": {
                                    "composite_media_id": 655,
                                    "customizations": []
                                },
                                "front": {
                                    "composite_media_id": 653,
                                    "customizations": []
                                },
                                "right": {
                                    "composite_media_id": 656,
                                    "customizations": []
                                }
                            },
                            "created_at": "2017-11-15T08:07:52+00:00",
                            "updated_at": "2017-11-15T08:07:55+00:00"
                        },
                        {
                            "id": 100,
                            "total": 121.2,
                            "price": 101.85,
                            "tax": 19.35,
                            "tax_rate": 19,
                            "attributes": [
                                {
                                    "name": "color",
                                    "value": "Pink"
                                },
                                {
                                    "name": "size",
                                    "value": "XL"
                                }
                            ],
                            "origin": {
                                "code": "6122944008004"
                            },
                            "media_id": 662,
                            "views": {
                                "back": {
                                    "composite_media_id": 664,
                                    "customizations": []
                                },
                                "left": {
                                    "composite_media_id": 665,
                                    "customizations": []
                                },
                                "front": {
                                    "composite_media_id": 663,
                                    "customizations": []
                                },
                                "right": {
                                    "composite_media_id": 666,
                                    "customizations": []
                                }
                            },
                            "created_at": "2017-11-15T08:07:53+00:00",
                            "updated_at": "2017-11-15T08:07:55+00:00"
                        },
                        {
                            "id": 102,
                            "total": 125.17,
                            "price": 116.98,
                            "tax": 8.19,
                            "tax_rate": 7,
                            "attributes": [
                                {
                                    "name": "color",
                                    "value": "Ivory"
                                },
                                {
                                    "name": "size",
                                    "value": "S"
                                }
                            ],
                            "origin": {
                                "code": "5897554102930"
                            },
                            "media_id": 672,
                            "views": {
                                "back": {
                                    "composite_media_id": 674,
                                    "customizations": []
                                },
                                "left": {
                                    "composite_media_id": 675,
                                    "customizations": []
                                },
                                "front": {
                                    "composite_media_id": 673,
                                    "customizations": []
                                },
                                "right": {
                                    "composite_media_id": 676,
                                    "customizations": []
                                }
                            },
                            "created_at": "2017-11-15T08:07:54+00:00",
                            "updated_at": "2017-11-15T08:07:55+00:00"
                        },
                        {
                            "id": 104,
                            "total": 64.28,
                            "price": 54.02,
                            "tax": 10.26,
                            "tax_rate": 19,
                            "attributes": [
                                {
                                    "name": "color",
                                    "value": "DarkSalmon"
                                },
                                {
                                    "name": "size",
                                    "value": "XL"
                                }
                            ],
                            "origin": {
                                "code": "0899276696853"
                            },
                            "media_id": 682,
                            "views": {
                                "back": {
                                    "composite_media_id": 684,
                                    "customizations": []
                                },
                                "left": {
                                    "composite_media_id": 685,
                                    "customizations": []
                                },
                                "front": {
                                    "composite_media_id": 683,
                                    "customizations": []
                                },
                                "right": {
                                    "composite_media_id": 686,
                                    "customizations": []
                                }
                            },
                            "created_at": "2017-11-15T08:07:55+00:00",
                            "updated_at": "2017-11-15T08:07:55+00:00"
                        }
                    ]
                }
            ],
            "links": {
                "first": "https://api.smake.io/v2/products?page=1",
                "last": "https://api.smake.io/v2/products?page=2",
                "prev": null,
                "next": "https://api.smake.io/v2/products?page=2"
            },
            "meta": {
                "current_page": 1,
                "from": 1,
                "last_page": 2,
                "path": "https://api.smake.io/v2/products",
                "per_page": 25,
                "to": 25,
                "total": 28
            }
        }';

        $decodedData = json_decode($rawData);
        // echo $jd;
        dd($decodedData);

        redirect()->route('products.dashboard');
    }

    public function dashboard() {
        return view('products.dashboard');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
