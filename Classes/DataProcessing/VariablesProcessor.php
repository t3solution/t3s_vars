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
 * Provides {vars} (raw), {varsLinked} (escaped, linked), {varsConfig} (maps),
 * {varsHours} (opening hours), {varsSocial} (profiles) and {varsIcons} (Font Awesome via EXT:iconpack) to Fluid.
 */
final readonly class VariablesProcessor implements DataProcessorInterface
{
    // variable key => Font Awesome icon (solid, available in fa5, fa6 and fa7)
    private const ICONS = [
        'phone' => 'mobile',
        'fax' => 'fax',
        'email' => 'envelope',
        'website' => 'globe',
        'maps' => 'map-marker-alt',
        'openingHours' => 'clock',
        'vcard' => 'address-card',
    ];

    // social key => Font Awesome brand icon
    private const BRANDS = [
        'facebook' => 'facebook-f',
        'instagram' => 'instagram',
        'linkedin' => 'linkedin-in',
        'xing' => 'xing',
        'youtube' => 'youtube',
        'x' => 'x-twitter',
        'tiktok' => 'tiktok',
        'mastodon' => 'mastodon',
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
        $request = $cObj->getRequest();
        $site = $request->getAttribute('site');
        $language = $request->getAttribute('language');
        $values = $this->variableService->getValues($site, $language);

        $linked = [];
        foreach ($values as $key => $value) {
            $linked[$key] = $this->variableService->render($key, $value, $site, $cObj, $language);
        }

        $settings = $site instanceof Site ? $site->getSettings() : null;
        [$icons, $brands] = $this->getIcons($settings, $processorConfiguration['icons.'] ?? []);

        $social = [];
        foreach ($this->variableService->getSocial($values) as $key => $profile) {
            $social[$key] = $profile + ['icon' => $brands[$key] ?? ''];
        }

        $processedData['vars'] = $values;
        $processedData['varsLinked'] = $linked;
        $processedData['varsHours'] = $this->variableService->openingHours($values['openingHours'] ?? '', $language);
        $processedData['varsSocial'] = $social;
        $processedData['varsConfig'] = [
            'mapsUrl' => $this->variableService->normalizeUrl($values['maps'] ?? ''),
            'mapsEmbed' => $this->embedUrl((string)$settings?->get('t3sVars.mapsEmbed', '')),
            'mapsConsent' => (bool)($settings?->get('t3sVars.mapsConsent', true) ?? true),
            'vcard' => (bool)($settings?->get('t3sVars.vcard', true) ?? true) ? $this->vcardLink($values) : null,
            'schemaType' => $this->schemaType((string)($settings?->get('t3sVars.jsonLdType', 'LocalBusiness') ?? '')),
        ];
        $processedData['varsIcons'] = $icons;
        return $processedData;
    }

    /**
     * Icon markup per variable key and per social profile, only if EXT:iconpack with Font Awesome is available.
     * Override icon names via TypoScript: dataProcessing.1760.icons.phone = phone
     *
     * @param array<string, mixed> $override
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    private function getIcons(?SiteSettings $settings, array $override): array
    {
        $factoryClass = 'Quellenform\\Iconpack\\IconpackFactory';
        if (!(bool)($settings?->get('t3sVars.icons', true) ?? true)
            || !ExtensionManagementUtility::isLoaded('iconpack')
            || !class_exists($factoryClass)
        ) {
            return [[], []];
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
            return [[], []];
        }
        $markup = static function (string $style, string $icon) use ($factory, $iconpack): string {
            try {
                return (string)$factory->getIconMarkup(
                    $iconpack . ':' . $style . ',' . $icon,
                    'native',
                    ['class' => 't3s-icon fa-fw', 'aria-hidden' => 'true']
                );
            } catch (\Throwable) {
                // icon not available in this iconpack version
                return '';
            }
        };
        $icons = [];
        foreach (array_replace(self::ICONS, array_filter($override, 'is_string')) as $key => $icon) {
            if (($html = $markup('solid', $icon)) !== '') {
                $icons[$key] = $html;
            }
        }
        $brands = [];
        foreach (self::BRANDS as $key => $icon) {
            if (($html = $markup('brands', $icon)) !== '') {
                $brands[$key] = $html;
            }
        }
        return [$icons, $brands];
    }

    /**
     * vCard as data URL plus file name, no server route needed.
     *
     * @param array<string, string> $values
     * @return array{href: string, filename: string}|null
     */
    private function vcardLink(array $values): ?array
    {
        $vcard = $this->variableService->vcard($values);
        if ($vcard === '') {
            return null;
        }
        $name = ($values['company'] ?? '') ?: ($values['person'] ?? '');
        $slug = trim((string)preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $name) ?: $name)), '-');
        return [
            'href' => 'data:text/vcard;charset=utf-8,' . rawurlencode($vcard),
            'filename' => ($slug !== '' ? $slug : 'contact') . '.vcf',
        ];
    }

    // schema.org type for microdata and JSON-LD; letters only
    private function schemaType(string $type): string
    {
        return preg_match('/^[A-Za-z]+$/', $type) ? $type : 'LocalBusiness';
    }

    // accepts the embed URL or the whole <iframe> code; only Google Maps embed URLs
    private function embedUrl(string $value): string
    {
        if (preg_match('#src=["\']([^"\']+)["\']#i', $value, $match)) {
            $value = $match[1];
        }
        $url = $this->variableService->normalizeUrl(html_entity_decode($value, ENT_QUOTES | ENT_HTML5));
        return preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed#i', $url) ? $url : '';
    }
}
