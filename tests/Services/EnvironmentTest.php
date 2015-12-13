<?php
namespace Versioner\Services;

use Versioner\Scm\Git;
use Versioner\TestCase;

class EnvironmentTest extends TestCase
{
    public function testCanCheckIfInRepository()
    {
        $env = new Environment(__DIR__, new Git());
        $this->assertFalse($env->isInRepository());

        $env = new Environment(__DIR__.'/../..', new Git());
        $this->assertTrue($env->isInRepository());
    }

    public function testCanCheckIfHasChangelog()
    {
        $env = new Environment(__DIR__, new Git());
        $this->assertFalse($env->hasChangelog());

        $env = new Environment(__DIR__.'/../..', new Git());
        $this->assertTrue($env->hasChangelog());
    }
}
