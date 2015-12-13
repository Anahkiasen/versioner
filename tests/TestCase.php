<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner;

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
