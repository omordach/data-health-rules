<?php

use UnionImpact\DataHealthPoc\DataHealthPocServiceProvider;
use UnionImpact\DataHealthPoc\Models\Rule;
use UnionImpact\DataHealthPoc\Tests\Models\Charge;
use UnionImpact\DataHealthPoc\Tests\Models\Member;

beforeEach(function () {
    config(['data-health-poc.metrics.enabled' => true]);
    (new DataHealthPocServiceProvider($this->app))->boot();

    Rule::factory()->create([
        'code' => 'DUE_OVER_MAX',
        'name' => 'Dues amount exceeds maximum',
        'options' => ['default_due' => 70, 'multiplier' => 2],
    ]);

    $member = Member::factory()->create([
        'typical_due' => 50,
        'status' => 'active',
    ]);

    Charge::factory()->create([
        'member_id' => $member->id,
        'period_ym' => '2025-01',
        'type' => 'dues',
        'amount' => 150,
    ]);
});

it('exposes open violations via metrics endpoint', function () {
    $this->artisan('data-health-poc:run')->assertExitCode(0);

    $response = $this->get('/metrics/data-health-poc');
    $response->assertStatus(200);
    $response->assertSee('# HELP data_health_poc_open', false);
    $response->assertSee('data_health_poc_open{rule="DUE_OVER_MAX"}', false);
});
