<?php

namespace UnionImpact\DataHealthPoc\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $table = 'dhp_results';
    protected $guarded = [];

    /**
     * @var array<string, string>
     */
    protected $casts = ['payload' => 'array'];
}
