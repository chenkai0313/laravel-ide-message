<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(MessageNoticeDatabaseSeeder::class);
        $this->call(MessagePushDatabaseSeeder::class);
        $this->call(MessageSmsDatabaseSeeder::class);
    }
}
