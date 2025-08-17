<?php

namespace UnionImpact\DataHealthPoc\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use UnionImpact\DataHealthPoc\Tests\Models\Charge;
use UnionImpact\DataHealthPoc\Tests\Models\Member;

/**
 * @extends Factory<Charge>
 */
class ChargeFactory extends Factory
{
    protected $model = Charge::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'period_ym' => $this->faker->date('Y-m'),
            'type' => 'dues',
            'amount' => $this->faker->randomFloat(2, 40, 200),
        ];
    }
}
