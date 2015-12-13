<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner;

use Symfony\Component\Console\Application;
use Versioner\Commands\CreateCommand;
use Versioner\Commands\IncrementCommand;

/**
 * @codeCoverageIgnore
 */
class Console extends Application
{
    /**
     * @var string
     */
    const VERSION = '0.1.0';

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('Versioner', self::VERSION);

        $this->add(new CreateCommand());
        $this->add(new IncrementCommand());
    }

    protected function detectScm()
    {
        $folder = getcwd();
    }
}
