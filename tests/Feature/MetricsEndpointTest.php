<?php

use Illuminate\Support\Facades\DB;
use UnionImpact\DataHealthPoc\DataHealthPocServiceProvider;
use UnionImpact\DataHealthPoc\Models\Rule;

beforeEach(function () {
    config(['data-health-poc.metrics.enabled' => true]);
    (new DataHealthPocServiceProvider($this->app))->boot();
});

it('exposes open violations via metrics endpoint', function () {
    Rule::create([
        'code' => 'DUE_OVER_MAX',
        'name' => 'Dues amount exceeds maximum',
        'options' => ['default_due' => 70, 'multiplier' => 2],
        'enabled' => true,
    ]);

    DB::table('members')->insert([
        'id' => 1,
        'status' => 'active',
        'typical_due' => 50,
    ]);

    DB::table('charges')->insert([
        'id' => 1,
        'member_id' => 1,
        'period_ym' => '2025-01',
        'type' => 'dues',
        'amount' => 150,
    ]);

    $this->artisan('data-health-poc:run')->assertExitCode(0);

    $response = $this->get('/metrics/data-health-poc');
    $response->assertStatus(200);
    $response->assertSee('# HELP data_health_poc_open', false);
    $response->assertSee('data_health_poc_open{rule="DUE_OVER_MAX"}', false);
});
