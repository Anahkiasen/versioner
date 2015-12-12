<?php

namespace ComposerVersioner;

use ComposerVersioner\Commands\CreateVersionCommand;
use Symfony\Component\Console\Application;

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
