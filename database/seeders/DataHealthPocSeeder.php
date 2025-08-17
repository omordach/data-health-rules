<?php

namespace UnionImpact\DataHealthPoc\Database\Seeders;

use Illuminate\Database\Seeder;
use UnionImpact\DataHealthPoc\Models\Rule;

class DataHealthPocSeeder extends Seeder
{
    public function run(): void
    {
        Rule::firstOrCreate(
            ['code' => 'DUE_OVER_MAX'],
            [
                'name'    => 'Dues amount exceeds maximum',
                'options' => ['default_due' => 70, 'multiplier' => 2],
                'enabled' => true,
            ]
        );

        Rule::firstOrCreate(
            ['code' => 'DUP_CHARGES'],
            [
                'name'    => 'Duplicate charges in same month',
                'options' => new \stdClass(),
                'enabled' => true,
            ]
        );
    }
}
