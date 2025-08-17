<?php

namespace UnionImpact\DataHealthPoc\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use UnionImpact\DataHealthPoc\Tests\Models\Member;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'status' => 'active',
            'typical_due' => $this->faker->randomFloat(2, 40, 100),
        ];
    }
}
