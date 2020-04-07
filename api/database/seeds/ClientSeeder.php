<?php

use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clients')->insert([
            'client_id' => env('CLIENT_TEST_ID', Str::random(10)),
            'client_secret' => env('CLIENT_TEST_SECRET', Str::random(10)),
        ]);
    }
}
