<?php

namespace UnionImpact\DataHealthPoc\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    protected $table = 'dhp_rules';
    protected $guarded = [];
    protected $casts = ['options' => 'array', 'enabled' => 'bool'];

    protected static function newFactory()
    {
        return \UnionImpact\DataHealthPoc\Database\Factories\RuleFactory::new();
    }
}
