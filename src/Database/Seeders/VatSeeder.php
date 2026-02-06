<?php

declare(strict_types=1);

namespace MetaFramework\Database\Seeders;

use Illuminate\Database\Seeder;
use MetaFramework\Models\Vat;

class VatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Vat::insert([
            [
                'rate' => 2000,
                'default' => 1
            ],
            [
                'rate' => 1000,
                'default' => null
            ],
            [
                'rate' => 550,
                'default' => null
            ],
            [
                'rate' => 210,
                'default' => null
            ],
            [
                'rate' => 0,
                'default' => null
            ],
        ]
        );
    }
}
