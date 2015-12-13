<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Services;

use Versioner\Scm\ScmInterface;

class Environment
{
    /**
     * @var ScmInterface
     */
    protected $scm;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @param string       $rootPath
     * @param ScmInterface $scm
     */
    public function __construct($rootPath, ScmInterface $scm)
    {
        $this->scm = $scm;
        $this->rootPath = $rootPath;
    }

    /**
     * @return bool
     */
    public function isInRepository()
    {
        $folder = $this->rootPath.DIRECTORY_SEPARATOR.$this->scm->getMetafolderName();

        return is_dir($folder);
    }

    /**
     * @return string
     */
    public function getChangelogPath()
    {
        return $this->rootPath.DIRECTORY_SEPARATOR.'CHANGELOG.md';
    }

    /**
     * @return bool
     */
    public function hasChangelog()
    {
        return file_exists($this->getChangelogPath());
    }
}
