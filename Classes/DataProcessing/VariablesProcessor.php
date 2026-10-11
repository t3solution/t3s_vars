<?php

declare(strict_types=1);

namespace T3S\T3sVars\DataProcessing;

use T3S\T3sVars\Service\VariableService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Provides {vars} (raw), {varsLinked} (escaped, linked), {varsConfig} (maps)
 * and {varsIcons} (Font Awesome via EXT:iconpack) to Fluid.
 */
final readonly class VariablesProcessor implements DataProcessorInterface
{
    // variable key => Font Awesome icon (available in fa5, fa6 and fa7)
    private const ICONS = [
        'phone' => 'mobile',
        'fax' => 'fax',
        'email' => 'envelope',
        'website' => 'globe',
        'maps' => 'map-marker-alt',
    ];

    public function __construct(private VariableService $variableService) {}

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $site = $cObj->getRequest()->getAttribute('site');
        $values = $this->variableService->getValues($site);

        $linked = [];
        foreach ($values as $key => $value) {
            $linked[$key] = $this->variableService->render($key, $value, $site, $cObj);
        }

        $settings = $site instanceof Site ? $site->getSettings() : null;
        $processedData['vars'] = $values;
        $processedData['varsLinked'] = $linked;
        $processedData['varsConfig'] = [
            'mapsUrl' => $this->httpUrl($values['maps'] ?? ''),
            'mapsEmbed' => $this->embedUrl((string)$settings?->get('t3sVars.mapsEmbed', '')),
            'mapsConsent' => (bool)($settings?->get('t3sVars.mapsConsent', true) ?? true),
        ];
        $processedData['varsIcons'] = $this->getIcons($settings, $processorConfiguration['icons.'] ?? []);
        return $processedData;
    }

    /**
     * Icon markup per variable key, only if EXT:iconpack with Font Awesome is available.
     * Override icon names via TypoScript: dataProcessing.1760.icons.phone = phone
     *
     * @param array<string, mixed> $override
     * @return array<string, string>
     */
    private function getIcons(?SiteSettings $settings, array $override): array
    {
        $factoryClass = 'Quellenform\\Iconpack\\IconpackFactory';
        if (!(bool)($settings?->get('t3sVars.icons', true) ?? true)
            || !ExtensionManagementUtility::isLoaded('iconpack')
            || !class_exists($factoryClass)
        ) {
            return [];
        }
        $factory = GeneralUtility::makeInstance($factoryClass);
        $iconpack = null;
        foreach (['fa7', 'fa6', 'fa5'] as $identifier) {
            if ($factory->isIconpackInstalled($identifier)) {
                $iconpack = $identifier;
                break;
            }
        }
        if ($iconpack === null) {
            return [];
        }
        $icons = [];
        foreach (array_replace(self::ICONS, array_filter($override, 'is_string')) as $key => $icon) {
            $markup = $factory->getIconMarkup(
                $iconpack . ':solid,' . $icon,
                'native',
                ['class' => 't3s-icon fa-fw', 'aria-hidden' => 'true']
            );
            if ($markup !== '') {
                $icons[$key] = $markup;
            }
        }
        return $icons;
    }

    // accepts the embed URL or the whole <iframe> code; only Google Maps embed URLs
    private function embedUrl(string $value): string
    {
        if (preg_match('#src=["\']([^"\']+)["\']#i', $value, $match)) {
            $value = $match[1];
        }
        $url = $this->httpUrl(html_entity_decode($value, ENT_QUOTES | ENT_HTML5));
        return preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed#i', $url) ? $url : '';
    }

    // add https:// if missing; only http(s) URLs are allowed in href/src
    private function httpUrl(string $url): string
    {
        $url = trim($url);
        if ($url !== '' && !preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        return preg_match('#^https?://#i', $url) ? $url : '';
    }
}
