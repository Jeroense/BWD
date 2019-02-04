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
        DB::table('TshirtMetrics')->insert([
            'size' => 'XS',
            'length_mm' => 687
        ]);
        DB::table('TshirtMetrics')->insert([
            'size' => 'S',
            'length_mm' => 711
        ]);
        DB::table('TshirtMetrics')->insert([
            'size' => 'M',
            'length_mm' => 737
        ]);
        DB::table('TshirtMetrics')->insert([
            'size' => 'L',
            'length_mm' => 762
        ]);
        DB::table('TshirtMetrics')->insert([
            'size' => 'XL',
            'length_mm' => 787
        ]);
        DB::table('TshirtMetrics')->insert([
            'size' => 'XXL',
            'length_mm' => 813
        ]);
        DB::table('TshirtMetrics')->insert([
            'size' => '3XL',
            'length_mm' => 838
        ]);
        DB::table('TshirtMetrics')->insert([
            'size' => '4XL',
            'length_mm' => 868
        ]);
        DB::table('TshirtMetrics')->insert([
            'size' => '5XL',
            'length_mm' => 899
        ]);
    }
}
