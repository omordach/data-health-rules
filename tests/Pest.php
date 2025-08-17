<?php

use UnionImpact\DataHealthPoc\Tests\TestCase;

require_once __DIR__.'/TestCase.php';

uses(TestCase::class)->in(
    __DIR__.'/RunDataHealthCommandTest.php',
    __DIR__.'/MetricsEndpointTest.php'
);
