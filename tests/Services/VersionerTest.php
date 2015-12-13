<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Services;

use Mockery;
use Symfony\Component\Console\Style\SymfonyStyle;
use Versioner\Changelog;
use Versioner\TestCase;

class VersionerTest extends TestCase
{
    public function testCanUpdateChangelog()
    {
        $output = $this->getMockOutput();

        file_put_contents($this->changelogPath, '# CHANGELOG');
        $versioner = new Versioner(new Changelog($this->changelogPath));
        $versioner->setOutput($this->getMockOutput());
        $versioner->createVersion('1.0.0');

        $expected = <<<'MARKDOWN'
# CHANGELOG

## 1.0.0 - {date}

### Added

- foobar

### Changed

- foobar
MARKDOWN;

        $expected = str_replace('{date}', date('Y-m-d'), $expected);
        $this->assertEquals($expected, file_get_contents($this->changelogPath));
    }

    public function testCanCreateReleaseFromExistingOne()
    {
        $existing = <<<'MARKDOWN'
# CHANGELOG

Description

## Unreleased - XXXX-XX-XX

### Added
- added

### Fixed
- fixed
MARKDOWN;

        file_put_contents($this->changelogPath, $existing);
        $versioner = new Versioner(new Changelog($this->changelogPath));
        $versioner->setOutput($this->getMockOutput());
        $versioner->setFrom('Unreleased');
        $versioner->createVersion('1.0.0');

        $expected = <<<'MARKDOWN'
# CHANGELOG

Description

## 1.0.0 - {date}

### Added

- added
- foobar

### Fixed

- fixed

### Changed

- foobar
MARKDOWN;

        $expected = str_replace('{date}', date('Y-m-d'), $expected);
        $this->assertEquals($expected, file_get_contents($this->changelogPath));
    }

    /**
     * @return Mockery\MockInterface
     */
    protected function getMockOutput()
    {
        $count = 0;
        $output = Mockery::mock(SymfonyStyle::class);
        $output->shouldReceive('ask')->andReturn(false);
        $output->shouldReceive('confirm')->with('This is your new CHANGELOG.md, all good?')->andReturn(true);
        $output->shouldReceive('confirm')->with('Push to remote?')->andReturn(false);
        $output->shouldReceive('askQuestion')->andReturnUsing(function () use (&$count) {
            ++$count;

            return ($count < 5 && $count % 2) ? 'foobar' : false;
        });
        $output->shouldIgnoreMissing();

        return $output;
    }
}
