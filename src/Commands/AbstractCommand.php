<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Versioner\Changelog;

abstract class AbstractCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var SymfonyStyle
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = new SymfonyStyle($input, $output);

        $this->fire();
    }

    /**
     * Run the command.
     */
    abstract protected function fire();

    /**
     * @return Changelog
     */
    protected function getChangelog()
    {
        $changelogPath = getcwd().DIRECTORY_SEPARATOR.'CHANGELOG.md';
        $changelog = new Changelog($changelogPath);
        if (!file_exists($changelogPath) && $this->output->confirm('No CHANGELOG.md exists, create it?')) {
            $stub = file_get_contents(__DIR__.'/../../stubs/CHANGELOG.md');
            file_put_contents($changelogPath, $stub);

            return $changelog;
        }

        return $changelog;
    }
}