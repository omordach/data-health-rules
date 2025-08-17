<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use UnionImpact\DataHealthPoc\Database\Seeders\DataHealthPocSeeder;
use UnionImpact\DataHealthPoc\DataHealthPocServiceProvider;
use UnionImpact\DataHealthPoc\Models\Result;
use UnionImpact\DataHealthPoc\Tests\Models\Charge;
use UnionImpact\DataHealthPoc\Tests\Seeders\MemberChargeSeeder;

class RunDataHealthCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [DataHealthPocServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->decimal('typical_due', 10, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->string('period_ym', 7);
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        $this->artisan('migrate', ['--database' => 'testing'])->run();
        (new DataHealthPocSeeder())->run();
        $this->seed(MemberChargeSeeder::class);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('charges');
        Schema::dropIfExists('members');
        parent::tearDown();
    }

    public function test_command_finds_and_resolves_violations(): void
    {
        $this->artisan('data-health-poc:run')->assertExitCode(0);

        $this->assertDatabaseHas('dhp_results', [
            'rule_code' => 'DUE_OVER_MAX',
            'entity_id' => '1',
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('dhp_results', [
            'rule_code' => 'DUP_CHARGES',
            'entity_id' => '1',
            'status' => 'open',
        ]);
        $this->assertEquals(2, Result::count());

        Charge::whereKey(1)->update(['amount' => 80]);
        Charge::whereKey(2)->delete();

        $this->artisan('data-health-poc:run')->assertExitCode(0);

        $this->assertDatabaseHas('dhp_results', [
            'rule_code' => 'DUE_OVER_MAX',
            'status' => 'resolved',
        ]);
        $this->assertDatabaseHas('dhp_results', [
            'rule_code' => 'DUP_CHARGES',
            'status' => 'resolved',
        ]);
        $this->assertEquals(2, Result::count());
    }
}
