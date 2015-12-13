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
use Versioner\Scm\Git;
use Versioner\Services\Environment;

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
     * @var Environment
     */
    protected $environment;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = new SymfonyStyle($input, $output);
        $this->environment = new Environment(getcwd(), new Git());

        if (!$this->environment->isInRepository()) {
            $this->output->error('Versioner needs to run in a folder with Git');

            return 1;
        }

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
        $changelogPath = $this->environment->getChangelogPath();
        if (!$this->environment->hasChangelog() && $this->output->confirm('No CHANGELOG.md exists, create it?')) {
            $stub = file_get_contents(__DIR__.'/../../stubs/CHANGELOG.md');
            file_put_contents($changelogPath, $stub);
        }

        return new Changelog($changelogPath);
    }
}
