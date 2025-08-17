<?php

namespace UnionImpact\DataHealthPoc\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use UnionImpact\DataHealthPoc\Models\Result;

/**
 * @extends Factory<Result>
 */
class ResultFactory extends Factory
{
    protected $model = Result::class;

    public function definition(): array
    {
        return [
            'rule_code' => 'TEST_RULE',
            'entity_type' => 'member',
            'entity_id' => (string) $this->faker->numberBetween(1, 999),
            'period_key' => '2025-01',
            'payload' => [],
            'hash' => $this->faker->uuid,
            'status' => 'open',
            'detected_at' => now(),
        ];
    }
}
