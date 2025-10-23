<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 */

namespace App\Command;

use HugaShop\Services\Helper;
use HugaShop\Services\NotifierFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'notifier:send', description: 'Dispatch notifier module send task.')]
class NotifierCommand extends Command
{

    /**
     * Configure command arguments.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('module', InputArgument::REQUIRED, 'Notifier module name.')
            ->addArgument('callback', InputArgument::REQUIRED, 'Base64 encoded callback.')
            ->addArgument('data', InputArgument::REQUIRED, 'Serialized message data.');
    }

    /**
     * Execute command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module             = (string) $input->getArgument('module');
        $callback           = (string) $input->getArgument('callback');
        $data               = (string) $input->getArgument('data');

        Helper::log('execute: ' . $module . ' ' . $callback . ' ' . $data);

        $serialized_callback = base64_decode($callback, true);
        if ($serialized_callback === false) {
            $output->writeln('<error>Invalid message content payload.</error>');
            return Command::FAILURE;
        }

        $serialized_data = base64_decode($data, true);
        if ($serialized_data === false) {
            $output->writeln('<error>Invalid message data data.</error>');
            return Command::FAILURE;
        }

        try {
            $message_data       = unserialize($serialized_data, ['allowed_classes' => true]);
            $message_callback   = unserialize($serialized_callback, ['allowed_classes' => true]);
        } catch (\Throwable) {
            $output->writeln('<error>Unable to unserialize message or callback data.</error>');
            return Command::FAILURE;
        }

        if (!is_array($message_data)) {
            $message_data = (array) $message_data;
        }

        if (!is_array($message_callback)) {
            $message_callback = (array) $message_callback;
        }

        $result = NotifierFactory::sendNotifier($module, $message_callback, $message_data);

        return $result === false ? Command::FAILURE : Command::SUCCESS;
    }
}
