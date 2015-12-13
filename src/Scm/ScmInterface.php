<?php

/*
 * This file is part of anahkiasen/versioner
 *
 * (c) madewithlove <heroes@madewithlove.be>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Versioner\Scm;

interface ScmInterface
{
    /**
     * @return string
     */
    public function getMetafolderName();
}
