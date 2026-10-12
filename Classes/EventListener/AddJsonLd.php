<?php

declare(strict_types=1);

namespace T3S\T3sVars\EventListener;

use T3S\T3sVars\Service\VariableService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;

/**
 * Adds schema.org JSON-LD (LocalBusiness etc.) before </head>, enabled via t3sVars.jsonLd.
 * Works with PAGE and PAGEVIEW, independent of the TypoScript page object.
 */
#[AsEventListener('t3s-vars/json-ld')]
final readonly class AddJsonLd
{
    public function __construct(private VariableService $variableService) {}

    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void
    {
        $request = $event->getRequest();
        $site = $request->getAttribute('site');
        if (!$site instanceof Site || !(bool)$site->getSettings()->get('t3sVars.jsonLd', false)) {
            return;
        }
        $content = $event->getContent();
        $position = stripos($content, '</head>');
        if ($position === false) {
            return;
        }
        $script = $this->build($site, $request->getAttribute('language'));
        if ($script !== '') {
            $event->setContent(substr_replace($content, $script . "\n", $position, 0));
        }
    }

    private function build(Site $site, mixed $language): string
    {
        $values = $this->variableService->getValues($site, $language);
        if (($values['company'] ?? '') === '') {
            return '';
        }
        $type = (string)$site->getSettings()->get('t3sVars.jsonLdType', 'LocalBusiness');
        $address = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $values['street'] ?? '',
            'postalCode' => $values['zip'] ?? '',
            'addressLocality' => $values['city'] ?? '',
            'addressCountry' => $values['country'] ?? '',
        ]);
        $hours = array_values(array_filter(array_column(
            $this->variableService->openingHours($values['openingHours'] ?? '', $language),
            'spec'
        )));
        $data = array_filter([
            '@context' => 'https://schema.org',
            '@type' => preg_match('/^[A-Za-z]+$/', $type) ? $type : 'LocalBusiness',
            'name' => $values['company'],
            'url' => $this->variableService->normalizeUrl($values['website'] ?? '') ?: (string)$site->getBase(),
            'telephone' => $values['phone'] ?? '',
            'faxNumber' => $values['fax'] ?? '',
            'email' => $values['email'] ?? '',
            'vatID' => $values['vatId'] ?? '',
            'address' => count($address) > 1 ? $address : [],
            'openingHours' => $hours,
            'hasMap' => $this->variableService->normalizeUrl($values['maps'] ?? ''),
            'sameAs' => array_values(array_column($this->variableService->getSocial($values), 'url')),
        ]);
        // JSON_HEX_TAG keeps "</script>" in values harmless
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_PRETTY_PRINT);
        return $json === false ? '' : '<script type="application/ld+json">' . $json . '</script>';
    }
}
