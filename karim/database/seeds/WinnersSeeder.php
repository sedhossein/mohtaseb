<?php

use Illuminate\Database\Seeder;

class WinnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('winners')->insertOrIgnore([
            'user_id' => 9106802437,
            'gift_id' => 1,
        ]);
    }
}
