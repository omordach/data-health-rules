<?php

namespace UnionImpact\DataHealthPoc\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $guarded = [];

    protected static function newFactory()
    {
        return \UnionImpact\DataHealthPoc\Database\Factories\MemberFactory::new();
    }
}
