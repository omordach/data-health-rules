<?php

namespace UnionImpact\DataHealthPoc\Rules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnionImpact\DataHealthPoc\Contracts\Rule as RuleContract;

class DuplicateMonthlyChargesRule implements RuleContract
{
    public static function code(): string { return 'DUP_CHARGES'; }
    public static function name(): string { return 'Duplicate charges in same month'; }

    public function evaluate(array $opt = []): Collection
    {
        $rows = DB::table('charges')
            ->selectRaw('member_id, period_ym, COUNT(*) as cnt, SUM(amount) as total_amount')
            ->where('type','dues')
            ->groupBy('member_id','period_ym')
            ->havingRaw('COUNT(*) >= 2')
            ->get();

        return $rows->map(function ($r) {
            $payload = ['count' => (int)$r->cnt, 'total_amount' => (float)$r->total_amount, 'period_ym' => $r->period_ym];
            $hash = sha1(json_encode(['r'=>'DUP_CHARGES','m'=>$r->member_id,'p'=>$r->period_ym,'c'=>$r->cnt], JSON_THROW_ON_ERROR));
            return [
                'entity_type' => 'member',
                'entity_id'   => (string)$r->member_id,
                'period_key'  => (string)$r->period_ym,
                'payload'     => $payload,
                'hash'        => $hash,
            ];
        });
    }
}
