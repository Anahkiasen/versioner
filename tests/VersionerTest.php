<?php
namespace ComposerVersioner;

use PHPUnit_Framework_TestCase;

class VersionerTest extends PHPUnit_Framework_TestCase
{
    public function testCanUpdateChangelog()
    {
        $versioner = new Versioner(__DIR__, '1.0.0');
        $versioner->createVersion();

        $expected = <<<'MARKDOWN'
# CHANGELOG

## 0.2.0 - 2015-01-01

### Added

- Initial `releases`

## 0.1.0 - 2015-01-01

### Added

- Initial `release`
MARKDOWN;

        $this->assertEquals($expected, file_get_contents(__DIR__.'/CHANGELOG.md'));
    }
}
