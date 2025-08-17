<?php

namespace UnionImpact\DataHealthPoc\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $table = 'dhp_rules';
    protected $guarded = [];
    /** @var array<string, string> */
    protected $casts = ['options' => 'array', 'enabled' => 'bool'];
}
