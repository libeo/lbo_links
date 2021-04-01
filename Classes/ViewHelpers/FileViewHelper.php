<?php
namespace Libeo\LboLinks\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Returns the size of the provided file in bytes
 *
 * @author Björn Fromme <fromme@dreipunktnull.com>, dreipunktnull
 * @package Vhs
 * @subpackage ViewHelpers\Media
 */
class FileViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @return void
     * @api
     */
    public function initializeArguments()
    {
        $this->registerArgument('reference', 'string', 'File reference', false, null);
    }

    /**
     * @return null|File|Folder
     */
    public function render()
    {
        $reference = $this->arguments['reference'];

        if (null === $reference) {
            $reference = $this->renderChildren();
            if (null === $reference) {
                return null;
            }
        }

        return $this->resolveFalReference($reference);
    }

    /**
     * @param $reference
     * @return null|File|Folder
     */
    private function resolveFalReference($reference)
    {
        try {
            $fileOrFolderObject = ResourceFactory::getInstance()->getFileObject($reference);
            // Link to a folder or file
            if ($fileOrFolderObject instanceof File || $fileOrFolderObject instanceof Folder) {
                return $fileOrFolderObject;
            }
        } catch (\RuntimeException $e) {
            // Element wasn't found
            return null;
        } catch (ResourceDoesNotExistException $e) {
            // Resource was not found
            return null;
        }
        return null;
    }
}
