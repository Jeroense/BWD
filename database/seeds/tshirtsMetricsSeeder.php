<?php

use Illuminate\Database\Seeder;

class tshirtMetricsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tshirtmetrics')->insert([
            'size' => 'XS',
            'length_mm' => 687
        ]);
        DB::table('tshirtmetrics')->insert([
            'size' => 'S',
            'length_mm' => 711
        ]);
        DB::table('tshirtmetrics')->insert([
            'size' => 'M',
            'length_mm' => 737
        ]);
        DB::table('tshirtmetrics')->insert([
            'size' => 'L',
            'length_mm' => 762
        ]);
        DB::table('tshirtmetrics')->insert([
            'size' => 'XL',
            'length_mm' => 787
        ]);
        DB::table('tshirtmetrics')->insert([
            'size' => 'XXL',
            'length_mm' => 813
        ]);
        DB::table('tshirtmetrics')->insert([
            'size' => '3XL',
            'length_mm' => 838
        ]);
        DB::table('tshirtmetrics')->insert([
            'size' => '4XL',
            'length_mm' => 868
        ]);
        DB::table('tshirtmetrics')->insert([
            'size' => '5XL',
            'length_mm' => 899
        ]);
    }
}
