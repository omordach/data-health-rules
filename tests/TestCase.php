<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use UnionImpact\DataHealthPoc\DataHealthPocServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [DataHealthPocServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status')->default('active');
            $table->decimal('typical_due', 10, 2)->nullable();
        });

        Schema::create('charges', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->string('period_ym', 7);
            $table->string('type');
            $table->decimal('amount', 10, 2);
        });

        $this->artisan('migrate');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('charges');
        Schema::dropIfExists('members');

        parent::tearDown();
    }
}
