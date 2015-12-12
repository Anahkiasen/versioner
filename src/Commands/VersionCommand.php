<?php
namespace ComposerVersioner\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('version')
             ->setDescription('Create a new version of the package')
             ->addArgument('version', InputArgument::REQUIRED, 'The version to create');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        dump(get_class($output)); exit;
    }
}
