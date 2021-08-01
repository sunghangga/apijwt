<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accounts')->insert(
            array(
                [
                    'ac_code' => '10000000', 
                    'name' => 'COMPANY CASH', 
                    'is_active' => 1
                ],
                [
                    'ac_code' => '20000000', 
                    'name' => 'CUSTOMER CASH', 
                    'is_active' => 1
                ]
            )
        );
    }
}
