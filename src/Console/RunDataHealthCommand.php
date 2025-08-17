<?php

namespace UnionImpact\DataHealthPoc\Console;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use UnionImpact\DataHealthPoc\Contracts\Rule as RuleContract;
use UnionImpact\DataHealthPoc\Models\{Rule, Result};

class RunDataHealthCommand extends Command
{
    protected $signature = 'data-health-poc:run {--rule=}';
    protected $description = 'Run PoC data health checks (single-tenant)';

    public function handle()
    {
        // Ensure rule rows exist (seed defaults if missing)
        $this->seedDefaults();

        $rules = Rule::query()->where('enabled', true)->get()->keyBy('code');

        // Map of rule code => class
        $builtIns = [
            'DUE_OVER_MAX' => \UnionImpact\DataHealthPoc\Rules\DuesOverMaxRule::class,
            'DUP_CHARGES'  => \UnionImpact\DataHealthPoc\Rules\DuplicateMonthlyChargesRule::class,
        ];

        $target = $this->option('rule');
        $now = CarbonImmutable::now();

        $summary = [];

        foreach ($builtIns as $code => $class) {
            if ($target && strcasecmp($target, $code) !== 0) continue;
            if (! $rules->has($code)) continue;

            /** @var RuleContract $rule */
            $rule = app($class);
            $opts = (array) ($rules[$code]->options ?? []);
            $violations = $rule->evaluate($opts);

            $openHashes = [];
            foreach ($violations as $v) {
                $openHashes[] = $v['hash'];
                Result::updateOrCreate(
                    ['hash' => $v['hash']],
                    [
                        'rule_code'   => $code,
                        'entity_type' => $v['entity_type'],
                        'entity_id'   => $v['entity_id'],
                        'period_key'  => $v['period_key'] ?? null,
                        'payload'     => $v['payload'] ?? [],
                        'status'      => 'open',
                        'detected_at' => $now,
                    ]
                );
            }

            // Auto-resolve items from this rule that are no longer present
            Result::where('rule_code', $code)
                ->where('status', 'open')
                ->when($openHashes, fn($q) => $q->whereNotIn('hash', $openHashes))
                ->update(['status' => 'resolved']);

            $summary[$code] = [
                'found' => $violations->count(),
                'open'  => Result::where('rule_code', $code)->where('status','open')->count(),
            ];
        }

        // Print summary to console/log
        foreach ($summary as $code => $s) {
            $this->info(sprintf('%s: found=%d, open=%d', $code, $s['found'], $s['open']));
        }

        return self::SUCCESS;
    }

    protected function seedDefaults(): void
    {
        Rule::firstOrCreate(
            ['code' => 'DUE_OVER_MAX'],
            ['name' => 'Dues amount exceeds maximum', 'options' => ['default_due' => 70, 'multiplier' => 2], 'enabled' => true]
        );

        Rule::firstOrCreate(
            ['code' => 'DUP_CHARGES'],
            ['name' => 'Duplicate charges in same month', 'options' => new \stdClass(), 'enabled' => true]
        );
    }
}
