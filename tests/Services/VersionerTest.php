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
        $versioner = new Versioner($this->mockChangelog());
        $versioner->setOutput($this->getMockOutput());
        $versioner->createVersion('1.0.0');

        $this->assertChangelogEquals(<<<'MARKDOWN'
# CHANGELOG

## [1.0.0] - {date}

### Added

- foobar

### Changed

- foobar
MARKDOWN
);
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

        $versioner = new Versioner($this->mockChangelog($existing));
        $versioner->setOutput($this->getMockOutput());
        $versioner->setFrom('Unreleased');
        $versioner->createVersion('1.0.0');

        $this->assertChangelogEquals(<<<'MARKDOWN'
# CHANGELOG

Description

## [1.0.0] - {date}

### Added

- added
- foobar

### Fixed

- fixed

### Changed

- foobar
MARKDOWN
);
    }

    public function testCanIncrementVersion()
    {
        $versioner = new Versioner($this->mockChangelog());
        $versioner->setOutput($this->getMockOutput());
        $versioner->incrementVersion(Versioner::MAJOR);

        $this->assertChangelogEquals(<<<'MARKDOWN'
# CHANGELOG

## [1.0.0] - {date}

### Added

- foobar

### Changed

- foobar
MARKDOWN
        );
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
        $output->shouldReceive('confirm')->with('This will create <comment>1.0.0</comment>, correct?')->andReturn(true);
        $output->shouldReceive('confirm')->with('Push to remote?')->andReturn(false);
        $output->shouldReceive('askQuestion')->andReturnUsing(function () use (&$count) {
            ++$count;

            return ($count < 5 && $count % 2) ? 'foobar' : false;
        });
        $output->shouldIgnoreMissing();

        return $output;
    }
}
