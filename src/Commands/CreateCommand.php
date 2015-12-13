<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Versioner\Services\Versioner;

/**
 * @codeCoverageIgnore
 */
class CreateCommand extends AbstractCommand
{
    /**
     * @var string
     */
    protected $rootPath;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('create')
             ->setDescription('Create a new version of the package')
             ->addArgument('version', InputArgument::REQUIRED, 'The version to create')
             ->addOption('from', null, InputOption::VALUE_REQUIRED, 'The version to create the new one from');
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $versioner = new Versioner($this->getChangelog());
        $versioner->setOutput($this->output);
        $versioner->createVersion($this->input->getArgument('version'));
    }
}
