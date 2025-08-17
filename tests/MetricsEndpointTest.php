<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\get;

use UnionImpact\DataHealthPoc\DataHealthPocServiceProvider;
use UnionImpact\DataHealthPoc\Models\Rule;

beforeEach(function (): void {
    config(['data-health-poc.metrics.enabled' => true]);
    (new DataHealthPocServiceProvider(app()))->boot();
});

it('exposes open violations via metrics endpoint', function () {
    Rule::query()->create([
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

    $exit = Artisan::call('data-health-poc:run');
    expect($exit)->toBe(0);

    $response = get('/metrics/data-health-poc');
    $response->assertStatus(200);
    $response->assertSee('# HELP data_health_poc_open', false);
    $response->assertSee('data_health_poc_open{rule="DUE_OVER_MAX"}', false);
});
