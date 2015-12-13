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
    /**
     * @var string
     */
    protected $changelogPath = __DIR__.'/CHANGELOG.md';

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
        if (file_exists($this->changelogPath)) {
            unlink($this->changelogPath);
        }
    }
}
