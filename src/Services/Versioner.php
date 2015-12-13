<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Services;

use Herrera\Version\Builder;
use League\CommonMark\CommonMarkConverter;
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
    const MAJOR = 'major';

    /**
     * @var string
     */
    const MINOR = 'minor';

    /**
     * @var string
     */
    const PATCH = 'patch';

    /**
     * @var Changelog
     */
    private $changelog;

    /**
     * @var SymfonyStyle
     */
    protected $output;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $from;

    /**
     * Versioner constructor.
     *
     * @param Changelog            $changelog
     * @param OutputInterface|null $output
     */
    public function __construct(Changelog $changelog, OutputInterface $output = null)
    {
        $this->changelog = $changelog;
        $this->output = $output ?: new NullOutput();

        // Wrap in SymfonyStyle
        if (!$this->output instanceof SymfonyStyle) {
            $this->output = new SymfonyStyle(new ArrayInput([]), $this->output);
        }
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////// CREATING VERSIONS /////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Create the package version.
     *
     * @param string $version
     */
    public function createVersion($version)
    {
        $this->version = $version;
        $this->output->title('Creating version '.$this->version);

        $steps = ['updateChangelog', 'pushTags'];
        foreach ($steps as $step) {
            if ($this->$step() === false) {
                return;
            }
        }

        $this->output->success('Version '.$this->version.' created');
    }

    /**
     * Increment the package version.
     *
     * @param string $increment
     */
    public function incrementVersion($increment)
    {
        $last = $this->changelog->getLastRelease();
        $last = $last ? $last['name'] : '0.0.0';

        $version = Builder::create()->importString($last);
        switch ($increment) {
            case self::MAJOR:
                $version->incrementMajor();
                break;

            case self::MINOR:
                $version->incrementMinor();
                break;

            default:
                $version->incrementPatch();
                break;
        }

        $version = (string) $version->getVersion();
        if (!$this->output->confirm('This will create <comment>'.$version.'</comment>, correct?')) {
            return;
        }

        $this->createVersion($version);
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// UPDATE CYCLE ////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return Changelog
     */
    protected function updateChangelog()
    {
        // If the release already exists and we don't want to overwrite it, cancel
        $question = 'Version <comment>'.$this->version.'</comment> already exists, create anyway?';
        if ($this->changelog->hasRelease($this->version) && !$this->output->confirm($question, false)) {
            return false;
        }

        // Summarize commits
        $this->summarizeCommits();

        // Gather changes for new version
        $this->output->section('Gathering changes for <comment>'.$this->version.'</comment>');
        $changes = $this->gatherChanges();
        if (!$changes) {
            $this->output->error('No changes to create version with');

            return false;
        }

        if ($this->from) {
            $from = $this->changelog->getRelease($this->from);
            $this->changelog->removeRelease($this->from);
            $changes = array_merge_recursive($from['changes'], $changes);
        }

        // Add to changelog
        $this->changelog->addRelease([
            'name' => $this->version,
            'date' => date('Y-m-d'),
            'changes' => $changes,
        ]);

        // Show to user and confirm
        $preview = $this->changelog->toMarkdown();
        $this->output->note($preview);
        if (!$this->output->confirm('This is your new CHANGELOG.md, all good?')) {
            return false;
        }

        // Write out to CHANGELOG.md
        $this->changelog->save();

        return $this->changelog;
    }

    /**
     * Summarize changes since last tag.
     */
    protected function summarizeCommits()
    {
        $last = $this->changelog->getLastRelease();
        if (!$last) {
            return;
        }

        $commits = $this->executeQuietly(['git', 'log', $last['name'].'..HEAD', '--oneline']);
        $commits = explode(PHP_EOL, trim($commits));

        if (!$commits) {
            $this->output->writeln('Commits since <comment>'.$last['name'].'</comment>:');
            $this->output->listing($commits);
        }
    }

    /**
     * Push the tags to Git.
     */
    protected function pushTags()
    {
        if (!$this->output->confirm('Tag and push to remote?')) {
            return false;
        }

        $commands = [
            ['git', 'add', '--all'],
            ['git', 'commit', '-vam', 'Create version '.$this->version],
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
     * Gather changes for the new version.
     *
     * @return array
     */
    protected function gatherChanges()
    {
        $converter = new CommonMarkConverter();

        $changes = [];
        foreach ($this->changelog->getSections() as $section) {
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

                $sectionChanges[] = $converter->convertToHtml($change);
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
