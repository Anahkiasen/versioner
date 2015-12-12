<?php
namespace ComposerVersioner\Services;

use ComposerVersioner\TestCase;
use Mockery;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class VersionerTest extends TestCase
{
    public function testCanUpdateChangelog()
    {
        $count = 0;

        $output = Mockery::mock(SymfonyStyle::class);
        $output->shouldReceive('ask')->andReturn(false);
        $output->shouldReceive('askQuestion')->andReturnUsing(function () use (&$count) {
            $count++;

            return ($count < 5 && $count % 2) ? 'foobar' : false;
        });
        $output->shouldIgnoreMissing();

        $versioner = new Versioner(__DIR__, '1.0.0', $output);
        $versioner->createVersion();

        $expected = <<<'MARKDOWN'
# CHANGELOG

This is your CHANGELOG

## 1.0.0 - 2015-12-12

### Added

- foobar

### Changed

- foobar
MARKDOWN;

        $this->assertEquals($expected, file_get_contents(__DIR__.'/CHANGELOG.md'));
    }
}
