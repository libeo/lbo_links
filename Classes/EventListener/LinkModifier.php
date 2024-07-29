<?php

namespace Libeo\LboLinks\EventListener;

use Libeo\LboLinks\Domain\Model\LinkConfiguration;
use Libeo\LboLinks\Domain\Model\LinkOverride;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Event\AfterLinkIsGeneratedEvent;

class LinkModifier
{
    public function __construct()
    {
        $frontendTypoScript = $GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.typoscript');
        if ($frontendTypoScript && $frontendTypoScript->hasSetup()) {
            $this->configuration = $frontendTypoScript->getSetupArray()['plugin.']['tx_lbolinks.'];
        }
    }

    public function __invoke(AfterLinkIsGeneratedEvent $event): void
    {
        $linkConfiguration = $this->getLinkConfiguration($event);
        $linkOverride = $this->getLinkOverride($event, $linkConfiguration);
        if ($linkOverride) {
            $event->setLinkResult($event->getLinkResult()->withLinkText(htmlspecialchars_decode($linkOverride->getContent())));

            if ($attrsArray = $this->tagStringToAttributesArray($linkOverride->getTag())) {
                $event->setLinkResult($event->getLinkResult()->withAttributes($attrsArray), true);
            }
        }
    }

    private function getLinkConfiguration(AfterLinkIsGeneratedEvent $event): LinkConfiguration
    {
        $configuration = new LinkConfiguration();

        $link = $event->getLinkResult();

        $configuration->setType($link->getType() === LinkService::TYPE_TELEPHONE ? 'tel' : $link->getType());
        $configuration->setUrl($link->getUrl());
        $configuration->setTarget($link->getTarget());
        $configuration->setText($link->getLinkText());
        $configuration->setClass($link->getAttribute('class'));
        $configuration->setTitle($link->getAttribute('title'));
        $configuration->setAttributes(GeneralUtility::implodeAttributes($link->getAttributes()));
        $configuration->setFile($link->getType() === LinkService::TYPE_FILE ? $this->getFileFromUrl($link->getUrl()) : null);

        return $configuration;
    }

    private function tagStringToAttributesArray(string $tag): ?array
    {
        libxml_use_internal_errors(true);
        $anchor = simplexml_load_string($tag .= '</a>');
        libxml_clear_errors();

        // simplexml_load_string returns false if the parsed HTML is invalid
        if (!$anchor) {
            return null;
        }

        return GeneralUtility::get_tag_attributes($tag);
    }

    private function getFileFromUrl(string $url): ?File
    {
        $defaultStorage = GeneralUtility::makeInstance(StorageRepository::class)->getDefaultStorage();

        try {
            $fileIdentifier =  str_starts_with($url, '/fileadmin/')
                ? substr($url, 10)
                : $url;

            return $defaultStorage->getFileByIdentifier($fileIdentifier);

        } catch (InsufficientFolderAccessPermissionsException $e) {
            // do nothing
        }
        return null;
    }

    private function getLinkOverride(AfterLinkIsGeneratedEvent $event, LinkConfiguration $linkConfiguration): ?LinkOverride
    {
        $overrideKey = $this->getOverrideKey($linkConfiguration);
        if ($overrideKey === false) {
            return null;
        }

        $localObjectRenderer = $event->getContentObjectRenderer();

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
        // Remove comments and new lines
        $linkOverride->setContent(
            preg_replace('/\n/', '',
                preg_replace('/<!--(.|\s)*?-->/', '', $linkOverride->getContent())
            )
        );

        return $linkOverride;
    }

    private function getOverrideKey(LinkConfiguration $linkConfiguration): mixed
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

    private function checkCondition(array $condition, LinkConfiguration $linkConfiguration): bool
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
