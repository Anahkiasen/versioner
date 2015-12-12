<?php
namespace ComposerVersioner;

use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->purgeStub();
    }

    public function tearDown()
    {
        $this->purgeStub();
    }

    protected function purgeStub()
    {
        $changelog = __DIR__.'/Services/CHANGELOG.md';
        if (file_exists($changelog)) {
            unlink($changelog);
        }
    }
}
