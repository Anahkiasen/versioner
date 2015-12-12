<?php

/*
 * This file is part of anahkiasen/composer-versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ComposerVersioner\Services;

use ComposerVersioner\Changelog;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\ProcessBuilder;

class Versioner
{
    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var SymfonyStyle
     */
    protected $output;

    /**
     * Versioner constructor.
     *
     * @param string               $rootPath
     * @param string               $version
     * @param OutputInterface|null $output
     */
    public function __construct($rootPath, $version, OutputInterface $output = null)
    {
        $this->rootPath = $rootPath;
        $this->version = $version;
        $this->output = $output ?: new NullOutput();

        // Wrap in SymfonyStyle
        if (!$this->output instanceof SymfonyStyle) {
            $this->output = new SymfonyStyle(new ArrayInput([]), $this->output);
        }
    }

    /**
     * Create the package version.
     */
    public function createVersion()
    {
        $this->output->title('Creating version '.$this->version);

        if (!$this->updateChangelog()) {
            return;
        }

        $this->updateCodebase();
        $this->pushTags();

        $this->output->success('Version '.$this->version. ' created');
    }

    /**
     * @return Changelog
     */
    protected function updateChangelog()
    {
        $changelogPath = $this->rootPath.'/CHANGELOG.md';
        $question = 'Version <comment>'.$this->version.'</comment> already exists, create anyway?';

        $changelog = $this->parseChangelog($changelogPath);
        if ($changelog->hasRelease($this->version) && !$this->output->confirm($question, false)) {
            return;
        }

        // Add changes
        $changes = $this->gatherChanges($changelog);
        if (!$changes) {
            return $this->output->error('No changes to create version with');
        }

        $changelog->addRelease([
            'name' => $this->version,
            'date' => date('Y-m-d'),
            'changes' => $changes,
        ]);

        $changelog->save();

        return $changelog;
    }

    /**
     * Update the VERSION constant in the codebase.
     */
    protected function updateCodebase()
    {
    }

    /**
     * Push the tags to Git.
     */
    protected function pushTags()
    {
        if (!$this->output->confirm('Push to remote?')) {
            return;
        }

        $commands = [
            'git commit -m "Create version '.$this->version.'"',
            'git tag '.$this->version,
            'git push',
            'git push --tags',
        ];

        $helper = new ProcessHelper();
        $helper->setHelperSet(new HelperSet([
            'debug_formatter' => new DebugFormatterHelper(),
        ]));

        foreach ($commands as $command) {
            $process = ProcessBuilder::create($command)->getProcess();
            $helper->run($this->output, $process);
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $changelogPath
     *
     * @return Changelog
     */
    protected function parseChangelog($changelogPath)
    {
        // Get all versions from CHANGELOG
        if (!file_exists($changelogPath) && !$this->output->ask('No CHANGELOG.md exists, create it?')) {
            file_put_contents($changelogPath, '# CHANGELOG'.PHP_EOL.'This is your CHANGELOG');
        }

        return new Changelog($changelogPath);
    }

    /**
     * @param Changelog $changelog
     *
     * @return array
     */
    protected function gatherChanges(Changelog $changelog)
    {
        $changes = [];
        foreach ($changelog->getSections() as $section) {
            $sectionChanges = [];

            // Prepare question
            $question = new Question('Add something to "'.ucfirst($section).'"?');
            $question->setValidator(function ($value) {
                return $value ?: 'NOPE';
            });

            // Gather changes from user
            while ($change = $this->output->askQuestion($question)) {
                if ($change === 'NOPE') {
                    break;
                }

                $sectionChanges[] = $change;
            }

            if ($sectionChanges) {
                $changes[$section] = $sectionChanges;
            }
        }

        return $changes;
    }
}
