<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Services;

use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Versioner\Changelog;

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

        $steps = ['updateChangelog', 'pushTags'];
        foreach ($steps as $step) {
            if (!$this->$step()) {
                return;
            }
        }

        $this->output->success('Version '.$this->version.' created');
    }

    /**
     * @return Changelog
     */
    protected function updateChangelog()
    {
        $changelogPath = $this->rootPath.'/CHANGELOG.md';

        // If the release already exists and we don't want to overwrite it, cancel
        $changelog = $this->parseChangelog($changelogPath);
        $question = 'Version <comment>'.$this->version.'</comment> already exists, create anyway?';
        if ($changelog->hasRelease($this->version) && !$this->output->confirm($question, false)) {
            return;
        }

        // Summarize commits
        $last = $changelog->getLastRelease();
        if ($last) {
            $commits = $this->executeQuietly(['git', 'log', $last['name'].'..HEAD', '--oneline']);
            $commits = explode(PHP_EOL, trim($commits));
            $this->output->writeln('Commits since <comment>'.$last['name'].'</comment>:');
            $this->output->listing($commits);
        }

        // Gather changes for new version
        $this->output->section('Gathering changes for <comment>'.$this->version.'</comment>');
        $changes = $this->gatherChanges($changelog);
        if (!$changes) {
            return $this->output->error('No changes to create version with');
        }

        // Add to changelog
        $changelog->addRelease([
            'name' => $this->version,
            'date' => date('Y-m-d'),
            'changes' => $changes,
        ]);

        // Show to user and confirm
        $preview = $changelog->toMarkdown();
        $this->output->note($preview);
        if (!$this->output->confirm('This is your new CHANGELOG.md, all good?')) {
            return;
        }

        // Write out to CHANGELOG.md
        $changelog->save();

        return $changelog;
    }

    /**
     * Push the tags to Git.
     */
    protected function pushTags()
    {
        if (!$this->output->confirm('Tag and push to remote?')) {
            return;
        }

        $commands = [
            ['git', 'commit', '-vam', '"Create version '.$this->version.'"'],
            ['git', 'tag', $this->version],
            ['git', 'push'],
            ['git', 'push', '--tags'],
        ];

        foreach ($commands as $command) {
            $this->execute($command);
        }
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// HELPERS ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Parse a CHANGELOG.md into an object.
     *
     * @param string $changelogPath
     *
     * @return Changelog
     */
    protected function parseChangelog($changelogPath)
    {
        // Get all versions from CHANGELOG
        if (!file_exists($changelogPath) && $this->output->confirm('No CHANGELOG.md exists, create it?')) {
            $stub = '# CHANGELOG';
            $stub .= PHP_EOL;
            $stub .= 'This project follows the [Semantic Versioning 2.0](http://semver.org/spec/v2.0.0.html) spec.';

            file_put_contents($changelogPath, $stub);
        }

        return new Changelog($changelogPath);
    }

    /**
     * Gather changes for the new version.
     *
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
            $question = new Question('Add something to "'.ucfirst($section).'"');
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

    /**
     * @param string $command
     *
     * @return string
     */
    protected function executeQuietly($command)
    {
        return $this->execute($command, false);
    }

    /**
     * Execute a bash command.
     *
     * @param string $command
     * @param bool   $showOutput
     *
     * @return string
     */
    protected function execute($command, $showOutput = true)
    {
        $helper = new ProcessHelper();
        $helper->setHelperSet(new HelperSet([
            'debug_formatter' => new DebugFormatterHelper(),
        ]));

        // Compute new verbosity
        $previousVerbosity = $this->output->getVerbosity();
        $verbosity = $showOutput ? OutputInterface::VERBOSITY_DEBUG : OutputInterface::VERBOSITY_QUIET;

        // Execute command with defined verbosity
        $this->output->setVerbosity($verbosity);
        $process = $helper->run($this->output, $command);
        $this->output->setVerbosity($previousVerbosity);

        return $process->getOutput();
    }
}
