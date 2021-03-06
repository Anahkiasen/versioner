<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner;

use Changelog\Parser;
use InvalidArgumentException;
use Versioner\Services\ChangelogConverter;

/**
 * An object representation of a CHANGELOG.
 */
class Changelog extends Parser
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var array
     */
    protected $releases = [];

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        parent::__construct(file_get_contents($file));

        $this->file = $file;
        $this->releases = $this->getReleases();
    }

    /**
     * @return array
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @return string|void
     */
    public function getDescription()
    {
        try {
            $description = parent::getDescription();
            if ($this->releases && strpos($description, $this->releases[0]['name']) !== false) {
                return;
            }
        } catch (InvalidArgumentException $exception) {
            return;
        }

        return $description;
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////// RELEASES //////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @param string $expected
     *
     * @return bool
     */
    public function hasRelease($expected)
    {
        return (bool) $this->getRelease($expected);
    }

    /**
     * Get a release in particular.
     *
     * @param string $expected
     *
     * @return array
     */
    public function getRelease($expected)
    {
        return array_first($this->releases, function ($key, $release) use ($expected) {
            return $release['name'] === $expected;
        });
    }

    /**
     * @param string $expected
     */
    public function removeRelease($expected)
    {
        $this->releases = array_filter($this->releases, function ($release) use ($expected) {
           return $release['name'] !== $expected;
        });
    }

    /**
     * @param array $release
     */
    public function addRelease(array $release)
    {
        array_unshift($this->releases, $release);
    }

    /**
     * @return array
     */
    public function getLastRelease()
    {
        return head($this->releases);
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// OUTPUT ///////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * @return string
     */
    public function toMarkdown()
    {
        $converter = new ChangelogConverter($this->releases, $this->getDescription());
        $markdown = $converter->getMarkdown();

        return $markdown;
    }

    /**
     * Save the new contents of the CHANGELOG
     * to the file.
     */
    public function save()
    {
        file_put_contents($this->file, $this->toMarkdown());
    }
}
