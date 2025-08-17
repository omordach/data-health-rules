<?php

namespace UnionImpact\DataHealthPoc\Contracts;

use Illuminate\Support\Collection;

interface Rule
{
    public static function code(): string;
    public static function name(): string;

    /**
     * Return collection of associative arrays:
     * ['entity_type','entity_id','period_key', 'payload'=>[], 'hash']
     *
     * @param array<string, mixed> $options
     * @return Collection<int, array<string, mixed>>
     */
    public function evaluate(array $options = []): Collection;
}
