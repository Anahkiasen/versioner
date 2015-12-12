<?php
namespace ComposerVersioner\Commands;

use Changelog\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class CreateVersionCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var SymfonyStyle
     */
    protected $output;

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
             ->addArgument('version', InputArgument::REQUIRED, 'The version to create');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input    = $input;
        $this->output   = new SymfonyStyle($input, $output);
        $this->rootPath = getcwd();

        $version = $this->input->getArgument('version');
        $this->output->title('Creating version '.$version);

        $this->updateChangelog($version);
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
            return $release['name'] === $this->input->getArgument('version');
        });

        if (!$exists) {
            $versions[] = [
                'name' => $this->input->getArgument('version'),
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
            'git commit -m "Create version '.$this->input->getArgument('version').'"',
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
