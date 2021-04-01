<?php

namespace Libeo\LboLinks\Domain\Model;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

class LinkOverride
{
    /**
     * @var string
     */
    protected $wrap = '';

    /**
     * @var string
     */
    protected $tag = '';

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @return string
     */
    public function getWrap()
    {
        return $this->wrap;
    }

    /**
     * @param string $wrap
     */
    public function setWrap($wrap)
    {
        $this->wrap = $wrap;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
