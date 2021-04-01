<?php
namespace Libeo\LboLinks\ViewHelpers\Format;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Copie du viewhelper f:format.bytes de Fluid.
 *
 * Cette version ajoute un espace insécable entre la taille et l'unité.
 */
class BytesViewHelper extends AbstractViewHelper implements ViewHelperInterface
{
    protected $escapeOutput = false;

    /**
     * @var array
     */
    protected static $units = [];

    /**
     * Render the supplied byte count as a human readable string.
     *
     * @param int $value The incoming data to convert, or NULL if VH children should be used
     * @param int $decimals The number of digits after the decimal point
     * @param string $decimalSeparator The decimal point character
     * @param string $thousandsSeparator The character for grouping the thousand digits
     * @return string Formatted byte count
     * @api
     */
    public function render($value = null, $decimals = 0, $decimalSeparator = '.', $thousandsSeparator = ',')
    {
        return static::renderStatic(
            [
                'value' => $value,
                'decimals' => $decimals,
                'decimalSeparator' => $decimalSeparator,
                'thousandsSeparator' => $thousandsSeparator
            ],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * Applies htmlspecialchars() on the specified value.
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $value = $arguments['value'];
        if ($value === null) {
            $value = $renderChildrenClosure();
        }

        if (empty(self::$units)) {
            self::$units = GeneralUtility::trimExplode(',', LocalizationUtility::translate('viewhelper.format.bytes.units', 'fluid'));
        }
        if (!is_integer($value) && !is_float($value)) {
            if (is_numeric($value)) {
                $value = (float)$value;
            } else {
                $value = 0;
            }
        }
        $bytes = max($value, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count(self::$units) - 1);
        $bytes /= pow(2, (10 * $pow));

        return sprintf(
            '%s&nbsp;%s',
            number_format(round($bytes, 4 * $arguments['decimals']), $arguments['decimals'], $arguments['decimalSeparator'], $arguments['thousandsSeparator']),
            self::$units[$pow]
        );
    }
}
