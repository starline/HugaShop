<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
            ->addArgument('content', InputArgument::REQUIRED, 'Base64 encoded message content.')
            ->addArgument('data', InputArgument::REQUIRED, 'Serialized message data.');
    }

    /**
     * Execute command.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module_name        = (string) $input->getArgument('module');
        $content_argument   = (string) $input->getArgument('content');
        $data_argument      = (string) $input->getArgument('data');

        $message_content = base64_decode($content_argument, true);
        if ($message_content === false) {
            $output->writeln('<error>Invalid message content payload.</error>');
            return Command::FAILURE;
        }

        $serialized_data = base64_decode($data_argument, true);
        if ($serialized_data === false) {
            $output->writeln('<error>Invalid message data data.</error>');
            return Command::FAILURE;
        }

        try {
            $message_data = unserialize($serialized_data, ['allowed_classes' => true]);
        } catch (\Throwable) {
            $output->writeln('<error>Unable to unserialize message data data.</error>');
            return Command::FAILURE;
        }

        if (!is_array($message_data)) {
            $message_data = (array) $message_data;
        }

        $module_class = sprintf('HugaShop\\Modules\\Notifier\\%s\\%s', $module_name, $module_name);

        if (!class_exists($module_class)) {
            $output->writeln(sprintf('<error>Notifier module %s not found.</error>', $module_name));
            return Command::FAILURE;
        }

        $result = $module_class::send($message_content, $message_data);

        return $result === false ? Command::FAILURE : Command::SUCCESS;
    }
}
