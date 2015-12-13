<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner;

class ChangelogTest extends TestCase
{
    public function testCanCheckIfHasRelease()
    {
        $changelog = new Changelog(__DIR__.'/../CHANGELOG.md');

        $this->assertTrue($changelog->hasRelease('0.1.0'));
        $this->assertFalse($changelog->hasRelease('9.9.9'));
    }
}
