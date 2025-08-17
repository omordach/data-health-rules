<?php

return [

    'rules' => [
        'DUE_OVER_MAX' => \UnionImpact\DataHealthPoc\Rules\DuesOverMaxRule::class,
        'DUP_CHARGES'  => \UnionImpact\DataHealthPoc\Rules\DuplicateMonthlyChargesRule::class,
    ],

    'metrics' => [
        // Enable or disable the built-in metrics route
        'enabled' => false,

        // Middleware(s) to wrap the metrics route, e.g. ['auth.basic']
        'middleware' => [],

    ],
];
