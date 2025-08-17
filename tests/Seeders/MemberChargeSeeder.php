<?php

namespace UnionImpact\DataHealthPoc\Tests\Seeders;

use Illuminate\Database\Seeder;
use UnionImpact\DataHealthPoc\Tests\Models\Charge;
use UnionImpact\DataHealthPoc\Tests\Models\Member;

class MemberChargeSeeder extends Seeder
{
    public function run(): void
    {
        $member = Member::factory()->create([
            'id' => 1,
            'typical_due' => 50,
            'status' => 'active',
        ]);

        Charge::factory()->create([
            'id' => 1,
            'member_id' => $member->id,
            'period_ym' => '2025-01',
            'type' => 'dues',
            'amount' => 150,
        ]);

        Charge::factory()->create([
            'id' => 2,
            'member_id' => $member->id,
            'period_ym' => '2025-01',
            'type' => 'dues',
            'amount' => 60,
        ]);
    }
}
