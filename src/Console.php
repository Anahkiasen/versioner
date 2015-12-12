<?php

/*
 * This file is part of anahkiasen/composer-versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ComposerVersioner;

use ComposerVersioner\Commands\CreateVersionCommand;
use Symfony\Component\Console\Application;

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
        parent::__construct('Composer Versioner', self::VERSION);

        $this->addCommands([
            new CreateVersionCommand(),
        ]);
    }
}
