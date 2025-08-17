<?php

namespace UnionImpact\DataHealthPoc\Console;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use UnionImpact\DataHealthPoc\Contracts\Rule as RuleContract;
use UnionImpact\DataHealthPoc\Models\{Result, Rule};

class RunDataHealthCommand extends Command
{
    protected $signature = 'data-health-poc:run {--rule=}';
    protected $description = 'Run PoC data health checks (single-tenant)';

    public function handle()
    {
        $rules = Rule::query()->where('enabled', true)->get()->keyBy('code');

        $configured = config('data-health-poc.rules', []);

        $target = $this->option('rule');
        $now = CarbonImmutable::now();

        $summary = [];

        foreach ($configured as $code => $class) {
            if ($target && strcasecmp($target, $code) !== 0) {
                continue;
            }
            if (! $rules->has($code)) {
                continue;
            }

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
                ->when($openHashes, fn ($q) => $q->whereNotIn('hash', $openHashes))
                ->update(['status' => 'resolved']);

            $summary[$code] = [
                'found' => $violations->count(),
                'open'  => Result::where('rule_code', $code)->where('status', 'open')->count(),
            ];
        }

        // Print summary to console/log
        foreach ($summary as $code => $s) {
            $this->info(sprintf('%s: found=%d, open=%d', $code, $s['found'], $s['open']));
        }

        return self::SUCCESS;
    }

}
