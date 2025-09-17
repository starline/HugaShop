<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 * 
 * 
 * Запуск комманды вручную
 * php bin/console cron:agents
 * 
 * Запуск комманды по cron
 * *\/5 * * * * /usr/bin/php /var/www/hugashop/bin/console cron:agents >> /var/www/hugashop/var/log/cron_agents.log 2>&1
 *
 */

namespace HugaShop\Addons\CronAgent\Command;

use HugaShop\Addons\CronAgent\CronAgent;
use HugaShop\Addons\CronAgent\Services\CronAgentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CronAgentsCommand extends Command
{
    protected static $defaultName = 'cron:agents';
    protected static $defaultDescription = 'Run Cron agents';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!CronAgent::isEnabled() || !CronAgent::getSettings('use_cron')) {
            $output->writeln('CronAgent addon disabled or not configured for cron.');
            return Command::FAILURE;
        }

        CronAgentService::run();
        $output->writeln('Agents executed.');
        return Command::SUCCESS;
    }
}
