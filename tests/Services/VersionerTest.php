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
use Versioner\TestCase;

class VersionerTest extends TestCase
{
    public function testCanUpdateChangelog()
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

        $versioner = new Versioner(__DIR__, '1.0.0', $output);
        $versioner->createVersion();

        $expected = <<<'MARKDOWN'
# CHANGELOG

This is your CHANGELOG

## 1.0.0 - {date}

### Added

- foobar

### Changed

- foobar
MARKDOWN;

        $expected = str_replace('{date}', date('Y-m-d'), $expected);
        $this->assertEquals($expected, file_get_contents(__DIR__.'/CHANGELOG.md'));
    }
}
