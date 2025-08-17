<?php

namespace UnionImpact\DataHealthPoc\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table = 'dhp_results';
    protected $guarded = [];
    protected $casts = ['payload' => 'array'];
}
