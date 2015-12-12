<?php
namespace ComposerVersioner\Commands;

use ComposerVersioner\Versioner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $rootPath = getcwd();
        $version  = $input->getArgument('version');
        $output   = new SymfonyStyle($input, $output);

        $output->title('Creating version '.$version);
        $versioner = new Versioner($rootPath, $version, $output);
        $versioner->createVersion();
    }
}
