<?php

use Pest\Plugin;
use Pest\Laravel\Plugin as LaravelPlugin;
use Tests\TestCase;

require __DIR__.'/tests/TestCase.php';

Plugin::uses(LaravelPlugin::class);

uses(TestCase::class)->in('tests/Feature', 'tests/Integration', 'tests/Unit');

