<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Addons\CronAgent\Services;

use HugaShop\Addons\CronAgent\Models\CronAgent as Agent;

final class CronAgentService
{

    /**
     * Run active agents
     */
    public static function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $agents = Agent::getList(['enabled' => 1], cache: null);
        foreach ($agents as $agent) {
            if (self::isDue($agent, $now)) {
                self::execute($agent);
            }
        }
    }


    /**
     * Check is agent due
     * @param object $agent
     * @param string $now
     */
    private static function isDue(object $agent, string $now): bool
    {
        if (empty($agent->start_at)) {
            return false;
        }

        if (empty($agent->last_run_at)) {
            return $now >= $agent->start_at;
        }

        $next = date(
            'Y-m-d H:i:s',
            strtotime($agent->last_run_at . " +{$agent->period_hours} hours +{$agent->period_minutes} minutes")
        );
        return $now >= $next;
    }


    /**
     * Execute agent function
     * @param object $agent
     */
    private static function execute(object $agent): void
    {
        $callable = str_replace('()', '', (string) $agent->function);
        if (str_contains($callable, '::')) {
            [$class, $method] = explode('::', $callable);
            if (class_exists($class) && method_exists($class, $method)) {
                try {
                    $class::$method();
                } catch (\Throwable) {
                    // Ignore errors
                }
            }
        }

        Agent::updateOne($agent->id, ['last_run_at' => date('Y-m-d H:i:s')]);
    }
}
