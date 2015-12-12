<?php
namespace ComposerVersioner;

use Changelog\Parser;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

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
     * @param string                     $rootPath
     * @param string               $version
     * @param OutputInterface|null $output
     */
    public function __construct($rootPath, $version, OutputInterface $output = null)
    {
        $this->rootPath = $rootPath;
        $this->version = $version;
        $this->output  = $output ?: new NullOutput();

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
        $this->pushTags();
    }

    /**
     * @return Parser
     */
    protected function updateChangelog()
    {
        $changelogPath = $this->rootPath.'/CHANGELOG.md';

        $changelog = $this->parseChangelog($changelogPath);
        $versions  = $changelog->getReleases();
        $exists    = array_filter($versions, function ($release) {
            return $release['name'] === $this->version;
        });

        if (!$exists) {
            $versions[] = [
                'name' => $this->version,
                'date' => date('Y-m-d'),
            ];
        }

        return $changelog;
    }

    /**
     * Push the tags to Git
     */
    protected function pushTags()
    {
        $commands = [
            'git commit -m "Create version '.$this->version.'"',
            'git push',
            'git push --tags',
        ];

        foreach ($commands as $command) {
            (new Process($command))->run();
        }
    }

    /**
     * @param string $changelogPath
     *
     * @return Parser
     */
    protected function parseChangelog($changelogPath)
    {
        // Get all versions from CHANGELOG
        if (!file_exists($changelogPath) && $this->output->ask('No CHANGELOG.md file found, create one?')) {
            file_put_contents($changelogPath, '# CHANGELOG');
        }

        // Parse changelog
        $changelog = file_get_contents($changelogPath);
        $parser    = new Parser($changelog);

        return $parser;
    }
}
