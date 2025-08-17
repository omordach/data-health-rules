<?php

return [
    'rules' => [
        'DUE_OVER_MAX' => \UnionImpact\DataHealthPoc\Rules\DuesOverMaxRule::class,
        'DUP_CHARGES'  => \UnionImpact\DataHealthPoc\Rules\DuplicateMonthlyChargesRule::class,
    ],
];
