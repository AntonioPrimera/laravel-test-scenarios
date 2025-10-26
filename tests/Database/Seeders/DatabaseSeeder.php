<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public static int $runCount = 0;

    public static function reset(): void
    {
        static::$runCount = 0;
    }

    public function run(): void
    {
        static::$runCount++;
    }
}
