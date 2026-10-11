<?php

declare(strict_types=1);

namespace T3S\T3sVars\Service;

use TYPO3\CMS\Core\Site\Entity\Site;
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
        'phone', 'fax', 'email', 'website', 'maps', 'vatId',
    ];
    private const PATTERN = '/\{\{([A-Za-z0-9_.-]+)(:text)?\}\}/';

    public function __construct(private SetRegistry $setRegistry) {}

    /**
     * @return array<string, string> key (without prefix) => value
     */
    public function getValues(mixed $site): array
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
    public function replace(string $content, mixed $site, ?ContentObjectRenderer $cObj): string
    {
        if (!str_contains($content, '{{')) {
            return $content;
        }
        $values = $this->getValues($site);
        if ($values === []) {
            return $content;
        }
        return (string)preg_replace_callback(
            self::PATTERN,
            fn(array $match): string => array_key_exists($match[1], $values)
                ? $this->render($match[1], $values[$match[1]], $site, ($match[2] ?? '') === '' ? $cObj : null)
                : $match[0],
            $content
        );
    }

    /**
     * Returns the escaped value, linked (tel/mail/url) if configured and $cObj is given.
     */
    public function render(string $key, string $value, mixed $site, ?ContentObjectRenderer $cObj): string
    {
        $text = htmlspecialchars($value);
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

    // "www.example.org" -> "https://www.example.org"; only http(s) is allowed
    private function normalizeUrl(string $value): string
    {
        $url = str_replace(' ', '%20', trim($value));
        if (!preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        return preg_match('#^https?://[^/\s]+#i', $url) ? $url : '';
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
}
