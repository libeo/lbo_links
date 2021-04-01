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

use TYPO3\CMS\Core\Resource\File;

class LinkConfiguration
{
    const TARGET_DEFAULT = 'default';
    const TARGET_BLANK = 'blank';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var string
     */
    protected $text = '';

    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $target = '';

    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $parameter = '';

    /**
     * @var string
     */
    protected $attributes = '';

    /**
     * @var File
     */
    protected $file = '';

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        if (substr($url, 0, 1) === '/') {
            $this->url = substr($url, 1);
        } else {
            $this->url = $url;
        }
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        $target = $this->target;
        if (empty($target)) {
            switch ($this->type) {
                case 'url':
                    $target = $GLOBALS['TSFE']->extTarget;
                    break;
                case 'file':
                    $target = $GLOBALS['TSFE']->fileTarget;
                    break;
            }
        }

        return $target;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Return configuration key for current target
     * @return string
     */
    public function getTargetName()
    {
        $target = $this->getTarget();

        if ($target == '_blank') {
            return self::TARGET_BLANK;
        } else {
            return self::TARGET_DEFAULT;
        }
    }

    /**
     * @return bool
     */
    public function getIsBlank()
    {
        return $this->getTarget() == '_blank';
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param string $parameter
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * @return string
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param $file
     */
    public function setFile($file = null)
    {
        $this->file = $file;
    }
}
