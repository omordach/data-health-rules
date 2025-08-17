<?php

use Illuminate\Support\Facades\DB;
use UnionImpact\DataHealthPoc\Rules\DuesOverMaxRule;

it('detects charges exceeding the configured maximum', function () {
    DB::table('members')->insert([
        ['id' => 1, 'typical_due' => 50, 'status' => 'active'],
    ]);

    DB::table('charges')->insert([
        ['id' => 1, 'member_id' => 1, 'period_ym' => '2025-01', 'type' => 'dues', 'amount' => 150],
        ['id' => 2, 'member_id' => 1, 'period_ym' => '2025-01', 'type' => 'dues', 'amount' => 80],
    ]);

    $rule = new DuesOverMaxRule();

    $results = $rule->evaluate();

    expect($results)->toHaveCount(1);

    $violation = $results->first();

    expect($violation['entity_type'])->toBe('member')
        ->and($violation['entity_id'])->toBe('1')
        ->and($violation['period_key'])->toBe('2025-01')
        ->and($violation['payload'])
            ->toMatchArray([
                'amount' => 150.0,
                'typical_due' => 50.0,
                'period_ym' => '2025-01',
            ]);
});
