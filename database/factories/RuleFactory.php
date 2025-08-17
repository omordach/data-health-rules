<?php

namespace UnionImpact\DataHealthPoc\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use UnionImpact\DataHealthPoc\Models\Rule;

/**
 * @extends Factory<Rule>
 */
class RuleFactory extends Factory
{
    protected $model = Rule::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->lexify('RULE????'),
            'name' => $this->faker->sentence,
            'options' => [],
            'enabled' => true,
        ];
    }
}
