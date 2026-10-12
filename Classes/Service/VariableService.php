<?php

declare(strict_types=1);

namespace T3S\T3sVars\Service;

use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\Set\SetRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Reads site settings "vars.*" and replaces {{key}} / {{key:text}} placeholders.
 */
final readonly class VariableService
{
    public const PREFIX = 'vars.';

    // order of the content element "Address" (frontend); other keys follow
    public const ORDER = [
        'profession', 'person', 'company', 'street', 'addressAddition', 'zip', 'city', 'country',
        'phone', 'fax', 'email', 'website', 'maps', 'openingHours', 'vatId',
        'facebook', 'instagram', 'linkedin', 'xing', 'youtube', 'x', 'tiktok', 'mastodon',
    ];

    // social media keys => display name
    public const SOCIAL = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'xing' => 'XING',
        'youtube' => 'YouTube',
        'x' => 'X',
        'tiktok' => 'TikTok',
        'mastodon' => 'Mastodon',
    ];

    private const PATTERN = '/\{\{([A-Za-z0-9_.-]+)(:text)?\}\}/';
    private const DAY = '(?:Mo|Tu|We|Th|Fr|Sa|Su)';
    private const LLL = 'LLL:EXT:t3s_vars/Resources/Private/Language/locallang.xlf:';

    public function __construct(
        private SetRegistry $setRegistry,
        private LanguageServiceFactory $languageServiceFactory,
    ) {}

    /**
     * Values of the site, overridden by t3sVars.translations for the given language.
     *
     * @return array<string, string> key (without prefix) => value
     */
    public function getValues(mixed $site, mixed $language = null): array
    {
        if (!$site instanceof Site) {
            return [];
        }
        $values = [];
        foreach ($site->getSettings()->getAllFlat() as $identifier => $value) {
            if (str_starts_with((string)$identifier, self::PREFIX) && is_scalar($value)) {
                $values[substr((string)$identifier, strlen(self::PREFIX))] = (string)$value;
            }
        }
        $language ??= $this->currentLanguage();
        if ($language instanceof SiteLanguage) {
            $code = $language->getLocale()->getLanguageCode();
            $translations = $this->parseTranslations((string)$site->getSettings()->get('t3sVars.translations', ''));
            $values = array_replace($values, $translations[$code] ?? []);
        }
        return $values;
    }

    /**
     * Labels from settings.definitions.yaml of all sets of the site.
     *
     * @return array<string, string> key (without prefix) => label
     */
    public function getLabels(mixed $site): array
    {
        if (!$site instanceof Site) {
            return [];
        }
        $labels = [];
        foreach ($this->setRegistry->getSets(...$site->getSets()) as $set) {
            foreach ($set->settingsDefinitions as $definition) {
                if (str_starts_with($definition->key, self::PREFIX)) {
                    $label = $GLOBALS['LANG']?->sL($definition->label) ?: $definition->label;
                    $labels[substr($definition->key, strlen(self::PREFIX))] = $label;
                }
            }
        }
        return $labels;
    }

    /**
     * Replaces all known placeholders in HTML; unknown placeholders stay untouched.
     */
    public function replace(string $content, mixed $site, ?ContentObjectRenderer $cObj, mixed $language = null): string
    {
        if (!str_contains($content, '{{')) {
            return $content;
        }
        $values = $this->getValues($site, $language);
        if ($values === []) {
            return $content;
        }
        return (string)preg_replace_callback(
            self::PATTERN,
            fn(array $match): string => array_key_exists($match[1], $values)
                ? $this->render($match[1], $values[$match[1]], $site, ($match[2] ?? '') === '' ? $cObj : null, $language)
                : $match[0],
            $content
        );
    }

    /**
     * Returns the escaped value, linked (tel/mail/url) if configured and $cObj is given.
     */
    public function render(string $key, string $value, mixed $site, ?ContentObjectRenderer $cObj, mixed $language = null): string
    {
        if ($key === 'openingHours') {
            return implode('<br>', array_map(
                static fn(array $line): string => htmlspecialchars($line['text']),
                $this->openingHours($value, $language)
            ));
        }
        $text = nl2br(htmlspecialchars($value), false);
        if ($cObj === null || $value === '' || !$site instanceof Site) {
            return $text;
        }
        $type = $this->getLinkType($key, $site);
        if ($type === 'url') {
            // plain anchor, independent of typolink handling of external URLs
            $href = $this->normalizeUrl($value);
            return $href === '' ? $text : '<a href="' . htmlspecialchars($href) . '" target="_blank" rel="noopener">' . $text . '</a>';
        }
        $parameter = match ($type) {
            'tel' => 'tel:' . preg_replace('/[^0-9+]/', '', $value),
            'mail' => 'mailto:' . trim($value),
            default => null,
        };
        return $parameter === null ? $text : (string)$cObj->typoLink($text, ['parameter' => $parameter]);
    }

    /**
     * One entry per line: "spec" is the schema.org value (null for free text), "text" the localized display.
     * "Mo-Fr 08:00-17:00" => ['spec' => 'Mo-Fr 08:00-17:00', 'text' => 'Mo–Fr 08:00–17:00 Uhr']
     *
     * @return list<array{spec: ?string, text: string}>
     */
    public function openingHours(string $value, mixed $language = null): array
    {
        $languageService = $this->languageService($language);
        $day = static fn(string $d): string => $languageService?->sL(self::LLL . 'day.' . $d) ?: $d;
        $pattern = '/^(' . self::DAY . '(?:-' . self::DAY . ')?(?:,' . self::DAY . '(?:-' . self::DAY . ')?)*)\s+(\d{1,2}:\d{2})-(\d{1,2}:\d{2})$/';
        $lines = [];
        // one period per line or separated by ";" (single-line field in the settings editor)
        foreach (preg_split('/\R|;/', $value) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (!preg_match($pattern, $line, $m)) {
                $lines[] = ['spec' => null, 'text' => $line];
                continue;
            }
            $days = (string)preg_replace_callback('/' . self::DAY . '/', static fn(array $d): string => $day($d[0]), $m[1]);
            $format = $languageService?->sL(self::LLL . 'hours.format') ?: '%s %s–%s';
            $lines[] = [
                'spec' => $line,
                'text' => sprintf($format, str_replace(['-', ','], ['–', ', '], $days), $m[2], $m[3]),
            ];
        }
        return $lines;
    }

    /**
     * Social media profiles with a valid URL.
     *
     * @param array<string, string> $values
     * @return array<string, array{name: string, url: string}>
     */
    public function getSocial(array $values): array
    {
        $social = [];
        foreach (self::SOCIAL as $key => $name) {
            $url = $this->normalizeUrl($values[$key] ?? '');
            if ($url !== '') {
                $social[$key] = ['name' => $name, 'url' => $url];
            }
        }
        return $social;
    }

    /**
     * vCard 3.0 (contact card) from the values; empty without company and person.
     *
     * @param array<string, string> $values
     */
    public function vcard(array $values): string
    {
        $v = static fn(string $key): string => trim($values[$key] ?? '');
        // escape per RFC 2426
        $e = static fn(string $text): string => str_replace(["\\", ',', ';', "\r\n", "\n"], ['\\\\', '\\,', '\\;', '\\n', '\\n'], $text);
        $person = $v('person');
        $company = $v('company');
        if ($person === '' && $company === '') {
            return '';
        }
        $parts = preg_split('/\s+/', $person) ?: [];
        $last = $person !== '' ? (string)array_pop($parts) : '';
        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:' . $e($last) . ';' . $e(implode(' ', $parts)) . ';;;',
            'FN:' . $e($person !== '' ? $person : $company),
        ];
        if ($company !== '') {
            $lines[] = 'ORG:' . $e($company);
        }
        if ($v('profession') !== '') {
            $lines[] = 'TITLE:' . $e($v('profession'));
        }
        if ($v('phone') !== '') {
            $lines[] = 'TEL;TYPE=WORK,VOICE:' . $e($v('phone'));
        }
        if ($v('fax') !== '') {
            $lines[] = 'TEL;TYPE=WORK,FAX:' . $e($v('fax'));
        }
        if ($v('email') !== '') {
            $lines[] = 'EMAIL;TYPE=INTERNET,WORK:' . $e($v('email'));
        }
        if (($url = $this->normalizeUrl($v('website'))) !== '') {
            $lines[] = 'URL:' . $e($url);
        }
        if ($v('street') . $v('zip') . $v('city') . $v('country') !== '') {
            $lines[] = 'ADR;TYPE=WORK:;' . $e($v('addressAddition')) . ';' . $e($v('street')) . ';' . $e($v('city'))
                . ';;' . $e($v('zip')) . ';' . $e($v('country'));
        }
        foreach ($this->getSocial($values) as $key => $profile) {
            $lines[] = 'X-SOCIALPROFILE;TYPE=' . $key . ':' . $e($profile['url']);
        }
        $lines[] = 'END:VCARD';
        return implode("\r\n", $lines) . "\r\n";
    }

    // "www.example.org" -> "https://www.example.org"; only http(s) is allowed
    public function normalizeUrl(string $value): string
    {
        $url = str_replace(' ', '%20', trim($value));
        if ($url === '') {
            return '';
        }
        if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        return preg_match('#^https?://[^/\s]+#i', $url) ? $url : '';
    }

    /**
     * "en.country = Switzerland; en.profession = President" => ['en' => ['country' => 'Switzerland', ...]]
     * Entries are separated by ";" or line breaks; opening hours use "|" for their own periods.
     *
     * @return array<string, array<string, string>>
     */
    private function parseTranslations(string $text): array
    {
        $translations = [];
        foreach (preg_split('/\R|;/', $text) ?: [] as $line) {
            if (preg_match('/^\s*([a-z]{2,3})\.([A-Za-z0-9_.-]+)\s*=\s*(.*?)\s*$/', $line, $m)) {
                $translations[$m[1]][$m[2]] = str_replace('|', ';', $m[3]);
            }
        }
        return $translations;
    }

    private function getLinkType(string $key, Site $site): ?string
    {
        $settings = $site->getSettings();
        foreach (['tel' => 'telKeys', 'mail' => 'mailKeys', 'url' => 'urlKeys'] as $type => $setting) {
            $keys = GeneralUtility::trimExplode(',', (string)$settings->get('t3sVars.' . $setting, ''), true);
            if (in_array($key, $keys, true)) {
                return $type;
            }
        }
        return null;
    }

    private function currentLanguage(): ?SiteLanguage
    {
        $language = ($GLOBALS['TYPO3_REQUEST'] ?? null)?->getAttribute('language');
        return $language instanceof SiteLanguage ? $language : null;
    }

    private function languageService(mixed $language): ?\TYPO3\CMS\Core\Localization\LanguageService
    {
        $language ??= $this->currentLanguage();
        if ($language instanceof SiteLanguage) {
            return $this->languageServiceFactory->createFromSiteLanguage($language);
        }
        return $GLOBALS['LANG'] ?? null;
    }
}
