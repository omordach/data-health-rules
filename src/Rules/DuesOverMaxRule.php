<?php

namespace UnionImpact\DataHealthPoc\Rules;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnionImpact\DataHealthPoc\Contracts\Rule as RuleContract;

class DuesOverMaxRule implements RuleContract
{
    public static function code(): string
    {
        return 'DUE_OVER_MAX';
    }
    public static function name(): string
    {
        return 'Dues amount exceeds configured maximum';
    }

    public function evaluate(array $opt = []): Collection
    {
        $multiplier = (float)($opt['multiplier'] ?? 2.0);
        $defaultDue = (float)($opt['default_due'] ?? 70.0);
        $periodStart = $opt['period_start'] ?? null;
        $periodEnd   = $opt['period_end']   ?? null;
        $status      = $opt['member_status'] ?? null;

        $sql = <<<'SQL'
WITH baseline AS (
  SELECT m.id AS member_id, m.status, COALESCE(m.typical_due, ?) AS typical_due
  FROM members m
)
SELECT c.member_id, c.period_ym, c.amount, b.typical_due
FROM charges c
JOIN baseline b ON b.member_id = c.member_id
WHERE c.type='dues' AND c.amount > b.typical_due * ?
SQL;

        $params = [$defaultDue, $multiplier];

        if ($status) {
            $sql     .= ' AND b.status = ?';
            $params[] = $status;
        }
        if ($periodStart) {
            $sql     .= ' AND c.period_ym >= ?';
            $params[] = $periodStart;
        }
        if ($periodEnd) {
            $sql     .= ' AND c.period_ym <= ?';
            $params[] = $periodEnd;
        }

        $rows = DB::select($sql, $params);

        return collect($rows)->map(function ($r) {
            $payload = ['amount' => (float)$r->amount, 'typical_due' => (float)$r->typical_due, 'period_ym' => $r->period_ym];
            $hash = sha1(json_encode(['r' => 'DUE_OVER_MAX', 'm' => $r->member_id, 'p' => $r->period_ym, 'a' => $r->amount], JSON_THROW_ON_ERROR));
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
