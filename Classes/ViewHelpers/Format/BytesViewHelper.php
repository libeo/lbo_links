<?php
namespace Libeo\LboLinks\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\ViewHelpers\Format\BytesViewHelper as FluidBytesViewHelper;

/**
 * Étant le viewhelper de Ext:Fluid pour ajouter un espace insécable entre la taille et l'unité.
 */
class BytesViewHelper extends FluidBytesViewHelper
{
    protected $escapeOutput = false;

    /**
     * Render the supplied byte count as a human readable string.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string Formatted byte count
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $arguments['units'] = $arguments['units'] ?? null; // Fix bug in PHP 8.1

        $render = parent::renderStatic($arguments, $renderChildrenClosure, $renderingContext);
        return str_replace(' ', '&nbsp;', $render);
    }
}
