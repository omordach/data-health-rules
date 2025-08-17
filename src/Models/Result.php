<?php

namespace UnionImpact\DataHealthPoc\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $table = 'dhp_results';
    protected $guarded = [];
    protected $casts = ['payload' => 'array'];

    protected static function newFactory()
    {
        return \UnionImpact\DataHealthPoc\Database\Factories\ResultFactory::new();
    }
}
