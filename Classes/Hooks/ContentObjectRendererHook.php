<?php
namespace Libeo\LboLinks\Hooks;

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


use Libeo\LboLinks\Domain\Model\LinkConfiguration;
use Libeo\LboLinks\Domain\Model\LinkOverride;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ContentObjectRendererHook
{

    public function __construct()
    {
        $this->configuration = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_lbolinks.'];
    }

    /**
     * @param array $params
     * @param ContentObjectRenderer $parent
     */
    public function typolinkPostProc(array $params, ContentObjectRenderer $parent)
    {
        $linkConfiguration = $this->getLinkConfiguration($params, $parent);
        $linkOverride = $this->getLinkOverride($linkConfiguration, $parent);
        if (!is_null($linkOverride)) {
            $params['linktxt'] = $linkOverride->getContent();
            $params['finalTag'] = $linkOverride->getTag();
            $params['conf']['wrap'] = $linkOverride->getWrap();
        }
    }

    /**
     * @param array $params
     * @param ContentObjectRenderer $parent
     * @return LinkConfiguration
     */
    private function getLinkConfiguration(array $params, ContentObjectRenderer $parent)
    {
        $configuration = new LinkConfiguration();

        if (strlen($params['finalTagParts']['url']) > 4 && substr($params['finalTagParts']['url'], 0, 4) == 'tel:') {
            $params['finalTagParts']['TYPE'] = 'tel';
        }

        $configuration->setType(isset($params['finalTagParts']['TYPE']) ? $params['finalTagParts']['TYPE'] : '');
        $configuration->setUrl(isset($params['finalTagParts']['url']) ? $params['finalTagParts']['url'] : '');
        $configuration->setTarget($params['tagAttributes']['target']);
        $configuration->setText(isset($params['linktxt']) ? $params['linktxt'] : '');
        $configuration->setClass($params['tagAttributes']['class']);
        $configuration->setTitle($params['tagAttributes']['title']);
        $configuration->setParameter($params['tagAttributes']['href']);
        $configuration->setAttributes(isset($params['finalTagParts']['aTagParams']) ? $params['finalTagParts']['aTagParams'] : '');
        $configuration->setFile(isset($params['linkDetails']['file']) ? $params['linkDetails']['file'] : null);

        return $configuration;
    }

    /**
     * @param LinkConfiguration $linkConfiguration
     * @return LinkOverride|null
     */
    private function getLinkOverride(LinkConfiguration $linkConfiguration, ContentObjectRenderer $parent)
    {
        $overrideKey = $this->getOverrideKey($linkConfiguration);
        if ($overrideKey == false) {
            return null;
        }

        /** @var ContentObjectRenderer $localObjectRenderer */
        $localObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        $localObjectRenderer->start(['link' => $linkConfiguration]);

        $html = $localObjectRenderer->cObjGetSingle(
            $this->configuration['types.'][$overrideKey]['rendering'],
            $this->configuration['types.'][$overrideKey]['rendering.']
        );

        $linkOverride = new LinkOverride();

        foreach (['TAG', 'CONTENT', 'WRAP'] as $partName) {
            $pattern = '/<!-- ' . $partName . ' -->(.*)<!-- ' . $partName . ' -->/s';

            if (preg_match($pattern, $html, $matches)) {
                $setter = 'set' . ucfirst(strtolower($partName));
                $linkOverride->$setter(trim($matches[1]));
            }
        }

        return $linkOverride;
    }

    /**
     * Check if an override is available for this link
     *
     * @param LinkConfiguration $linkConfiguration
     * @return bool
     */
    private function getOverrideKey(LinkConfiguration $linkConfiguration)
    {
        if (isset($this->configuration['types.'])) {
            foreach ($this->configuration['types.'] as $typeKey => $typeConf) {
                if ($this->checkCondition($typeConf['condition.'], $linkConfiguration)) {
                    return $typeKey;
                }
            }
        }

        return false;
    }

    private function checkCondition(array $condition, LinkConfiguration $linkConfiguration)
    {
        if (isset($condition['class'])) {
            $classes = GeneralUtility::trimExplode(',', $condition['class']);
            if (!in_array($linkConfiguration->getClass(), $classes)) {
                return false;
            }
        }
        if (isset($condition['target'])) {
            if ($condition['target'] != $linkConfiguration->getTargetName()) {
                return false;
            }
        }
        if (isset($condition['type'])) {
            $types = GeneralUtility::trimExplode(',', $condition['type']);
            if (!in_array($linkConfiguration->getType(), $types)) {
                return false;
            }
        }
        if (isset($condition['regex'])) {
            preg_match($condition['regex'], $linkConfiguration->getUrl(), $match);
            if (!isset($match[0])) {
                return false;
            }
        }

        return true;
    }
}
