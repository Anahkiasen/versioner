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

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// LIFECYCLE /////////////////////////////
    //////////////////////////////////////////////////////////////////////

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

    //////////////////////////////////////////////////////////////////////
    ///////////////////////////// ASSERTIONS /////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $expected
     */
    protected function assertChangelogEquals($expected)
    {
        $expected = str_replace('{date}', date('Y-m-d'), $expected);
        $this->assertEquals($expected, file_get_contents($this->changelogPath));
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// MOCKS ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $contents
     *
     * @return Changelog
     */
    protected function mockChangelog($contents = '# CHANGELOG')
    {
        file_put_contents($this->changelogPath, $contents);

        return new Changelog($this->changelogPath);
    }
}
