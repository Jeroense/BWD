<?php

namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Traits\SmakeApi;
use App\Http\Traits\DebugLog;
use App\CustomVariant;
use App\Order;
use App\OrderItem;
use App\Customer;
use App\PostAddress;
use App\ProductAttribute;
use App\AttributeValue;

class Bolservices
{
    public $logFile = 'public/logs/message.txt';

    use DebugLog;
    use BolAPi; 

    public function buildProductFeed($customVariantIds)
    {
        $feed_file_name = "Borduurwerkdeal" . time();
        $feed_header = '';
        $products = ProductAttribute::with('attrValues')->get();

        foreach ($products as $product) 
        {
            $feed_header .= $product->product_attribute_key ."\t";
        }

        $file = fopen(env('CONTENTFEED_PATH', '') . '/' .  $feed_file_name  . '.txt', 'w');
        fwrite($file, $feed_header . PHP_EOL);

        foreach ($customVariantIds as $id) 
        {
            $product_data = '';
            $shirt = CustomVariant::find($id);

            foreach ($products as $product) {
                if (strpos($product->attrValues->attr_value, '->') != false) {
                    $column = substr($product->attrValues->attr_value, strpos($product->attrValues->attr_value, '>') + 1);
                    $product_data .= strtoupper($shirt->$column) != 'XXXL' ? $shirt->$column . "\t" : $product_data .= '3XL' . "\t";
                } else {
                    $product_data .= $product->attrValues->attr_value . "\t";
                }
            }

            $shirt->update(['isPublishedAtBol' => 'pending']);
            fwrite($file, $product_data . PHP_EOL);
        }

        fclose($file);
        return $feed_file_name;
    }

    public function getBolOrders() // array
    {
        $orders = [];
        // $orders = ......

        return $orders;
    }
}