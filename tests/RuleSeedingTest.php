<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;

use function Pest\Laravel\seed;

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

beforeEach(function (): void {
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

    Artisan::call('migrate');

    seed(DataHealthPocSeeder::class);
});

it('seeds default rules', function (): void {
    $exit = Artisan::call('data-health-poc:run');
    expect($exit)->toBe(0);

    expect(Rule::query()->where('code', 'DUE_OVER_MAX')->exists())->toBeTrue()
        ->and(Rule::query()->where('code', 'DUP_CHARGES')->exists())->toBeTrue();
});
