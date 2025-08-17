<?php

namespace UnionImpact\DataHealthPoc\Rules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnionImpact\DataHealthPoc\Contracts\Rule as RuleContract;

class DuplicateMonthlyChargesRule implements RuleContract
{
    public static function code(): string
    {
        return 'DUP_CHARGES';
    }
    public static function name(): string
    {
        return 'Duplicate charges in same month';
    }

    /**
     * @param array<string, mixed> $opt
     * @return Collection<int, array<string, mixed>>
     */
    public function evaluate(array $opt = []): Collection
    {
        $periodStart = $opt['period_start'] ?? null;
        $periodEnd   = $opt['period_end']   ?? null;
        $status      = $opt['member_status'] ?? null;

        $query = DB::table('charges as c')
            ->selectRaw('c.member_id, c.period_ym, COUNT(*) as cnt, SUM(c.amount) as total_amount')
            ->join('members as m', 'm.id', '=', 'c.member_id')
            ->where('c.type', 'dues');

        if ($status) {
            $query->where('m.status', $status);
        }
        if ($periodStart) {
            $query->where('c.period_ym', '>=', $periodStart);
        }
        if ($periodEnd) {
            $query->where('c.period_ym', '<=', $periodEnd);
        }

        $rows = $query->groupBy('c.member_id', 'c.period_ym')
            ->havingRaw('COUNT(*) >= 2')
            ->get();

        return $rows->map(function ($r) {
            $payload = ['count' => (int)$r->cnt, 'total_amount' => (float)$r->total_amount, 'period_ym' => $r->period_ym];
            $hash = sha1(json_encode(['r' => 'DUP_CHARGES','m' => $r->member_id,'p' => $r->period_ym,'c' => $r->cnt], JSON_THROW_ON_ERROR));
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
