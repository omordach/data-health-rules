<?php

namespace UnionImpact\DataHealthPoc\Http;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function __invoke()
    {
        $series = DB::table('dhp_results')
            ->selectRaw('rule_code, COUNT(*) as cnt')
            ->where('status','open')
            ->groupBy('rule_code')
            ->get();

        $out = [];
        $out[] = "# HELP data_health_poc_open Open violations by rule";
        $out[] = "# TYPE data_health_poc_open gauge";
        foreach ($series as $row) {
            $out[] = sprintf('data_health_poc_open{rule="%s"} %d', $row->rule_code, $row->cnt);
        }
        return new Response(implode("\n", $out)."\n", 200, ['Content-Type' => 'text/plain; version=0.0.4']);
    }
}
