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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

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
     * {@inheritdoc}
     */
    public function createVersion()
    {
        $this->output->title('Creating version '.$this->version);

        $this->updateChangelog();
        //$this->pushTags();
    }

    /**
     * @return Changelog
     */
    protected function updateChangelog()
    {
        $changelogPath = $this->rootPath.'/CHANGELOG.md';

        $changelog = $this->parseChangelog($changelogPath);
        if (!$changelog->hasRelease($this->version)) {

            // Add changes
            $changes = [];
            foreach ($changelog->getSections() as $section) {
                $changes[$section] = [];

                $question = new Question('Add something to "'.$section.'"?');
                $question->setValidator(function ($value) {
                    return $value ?: 'NOPE';
                });

                while ($change = $this->output->askQuestion($question)) {
                    if ($change === 'NOPE') {
                        break;
                    }

                    $changes[$section][] = $change;
                }
            }

            $changelog->addRelease([
                'name' => $this->version,
                'date' => date('Y-m-d'),
                'changes' => $changes,
            ]);
        }

        $changelog->save();

        return $changelog;
    }

    /**
     * Push the tags to Git.
     */
    protected function pushTags()
    {
        $commands = [
            'git commit -m "Create version '.$this->version.'"',
            'git push',
            'git push --tags',
        ];

        foreach ($commands as $command) {
            //(new Process($command))->run();
        }
    }

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
}
