<?php
namespace ComposerVersioner;

use Changelog\Parser;
use ComposerVersioner\Services\ChangelogConverter;

/**
 * An object representation of a CHANGELOG
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
     * Changelog constructor.
     *
     * @param string $file
     */
    public function __construct($file)
    {
        parent::__construct(file_get_contents($file));

        $this->file     = $file;
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
     * @param string $expected
     *
     * @return bool
     */
    public function hasRelease($expected)
    {
        return (bool) array_filter($this->releases, function ($release) use ($expected) {
            return $release['name'] === $expected;
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
     * Save the new contents of the CHANGELOG
     * to the file
     *
     * @return string
     */
    public function save()
    {
        $converter = new ChangelogConverter($this->releases, $this->getDescription());
        $markdown  = $converter->getMarkdown();

        file_put_contents($this->file, $markdown);
    }
}
