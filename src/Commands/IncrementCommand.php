<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Commands;

use Symfony\Component\Console\Input\InputOption;
use Versioner\Services\Versioner;

class IncrementCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('increment')
             ->setDescription('Increment a part of the version')
             ->addOption('major', 'M', InputOption::VALUE_NONE)
             ->addOption('minor', 'm', InputOption::VALUE_NONE)
             ->addOption('patch', 'P', InputOption::VALUE_NONE);
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $increment = Versioner::PATCH;
        if ($this->input->getOption('major')) {
            $increment = Versioner::MAJOR;
        } elseif ($this->input->getOption('minor')) {
            $increment = Versioner::MINOR;
        }

        $versioner = new Versioner($this->getChangelog());
        $versioner->setOutput($this->output);
        $versioner->incrementVersion($increment);
    }
}
