<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use UnionImpact\DataHealthPoc\Database\Seeders\DataHealthPocSeeder;
use UnionImpact\DataHealthPoc\Models\Rule;

class RuleSeedingTestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [\UnionImpact\DataHealthPoc\DataHealthPocServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

    }
}

uses(RuleSeedingTestCase::class);

beforeEach(function () {
    Schema::create('members', function (Blueprint $t) {
        $t->increments('id');
        $t->string('status')->nullable();
        $t->decimal('typical_due', 10, 2)->nullable();
    });

    Schema::create('charges', function (Blueprint $t) {
        $t->increments('id');
        $t->unsignedInteger('member_id');
        $t->string('period_ym');
        $t->string('type');
        $t->decimal('amount', 10, 2);
    });

    $this->artisan('migrate')->run();

    $this->seed(DataHealthPocSeeder::class);
});

it('seeds default rules', function () {
    $this->artisan('data-health-poc:run')->assertExitCode(0);

    expect(Rule::where('code', 'DUE_OVER_MAX')->exists())->toBeTrue()
        ->and(Rule::where('code', 'DUP_CHARGES')->exists())->toBeTrue();
});
