<?php
namespace Libeo\LboLinks\ViewHelpers\Format;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * TYPO3\CMS\Fluid\ViewHelpers\Format\BytesViewHelper is now a final class, so we can't extend it anymore.
 * We copy the whole implementation to be able to replace blank spaces with &nbsp
 */
class BytesViewHelper extends AbstractViewHelper
{
    /**
     * Output is escaped already. We must not escape children, to avoid double encoding.
     *
     * @var bool
     */
    protected $escapeChildren = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'int', 'The incoming data to convert, or NULL if VH children should be used');
        $this->registerArgument('decimals', 'int', 'The number of digits after the decimal point', false, 0);
        $this->registerArgument('decimalSeparator', 'string', 'The decimal point character', false, '.');
        $this->registerArgument('thousandsSeparator', 'string', 'The character for grouping the thousand digits', false, ',');
        $this->registerArgument('units', 'string', 'comma separated list of available units, default is LocalizationUtility::translate(\'viewhelper.format.bytes.units\', \'fluid\')');
    }

    /**
     * Render the supplied byte count as a human-readable string.
     */
    public function render(): string
    {
        if ($this->arguments['units'] ?? null) {
            $units = $this->arguments['units'];
        } else {
            $units = LocalizationUtility::translate('viewhelper.format.bytes.units', 'fluid');
        }
        $units = GeneralUtility::trimExplode(',', (string)$units, true);
        $value = $this->renderChildren();
        if (is_numeric($value)) {
            $value = (float)$value;
        }
        if (!is_int($value) && !is_float($value)) {
            $value = 0;
        }
        $bytes = max($value, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= 2 ** (10 * $pow);
        $render = sprintf(
            '%s %s',
            number_format(
                round($bytes, 4 * $this->arguments['decimals']),
                (int)$this->arguments['decimals'],
                $this->arguments['decimalSeparator'],
                $this->arguments['thousandsSeparator']
            ),
            $units[$pow]
        );
        return str_replace(' ', '&nbsp;', $render);
    }

    /**
     * Explicitly set argument name to be used as content.
     */
    public function getContentArgumentName(): string
    {
        return 'value';
    }
}
