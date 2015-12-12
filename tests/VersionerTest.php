<?php
namespace ComposerVersioner;

use PHPUnit_Framework_TestCase;

class VersionerTest extends PHPUnit_Framework_TestCase
{
    public function testCanUpdateChangelog()
    {
        $versioner = new Versioner(__DIR__, '1.0.0');
        $versioner->createVersion();
    }
}
