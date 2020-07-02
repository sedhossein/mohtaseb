<?php

use Illuminate\Database\Seeder;

class GiftCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('gift_codes')->insert([
            'code' => 'IRAN2020',
            'remaining' => 5,
            'value' => 1000,
            'description' => 'Viva Iran',
        ]);
        \Illuminate\Support\Facades\DB::table('gift_codes')->insert([
            'code' => 'IRAN',
            'remaining' => 1000,
            'value' => 50000,
            'description' => 'To Support Iran',
        ]);
    }
}
