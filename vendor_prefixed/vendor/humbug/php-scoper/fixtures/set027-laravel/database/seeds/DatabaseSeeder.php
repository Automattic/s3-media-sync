<?php

namespace WPCOM_VIP;

use WPCOM_VIP\Illuminate\Database\Seeder;
class DatabaseSeeder extends \WPCOM_VIP\Illuminate\Database\Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
    }
}
\class_alias('WPCOM_VIP\\DatabaseSeeder', 'DatabaseSeeder', \false);
