<?php

declare(strict_types=1);

namespace T3S\T3sVars\EventListener;

use T3S\T3sVars\Service\VariableService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\RteCKEditor\Form\Element\Event\AfterPrepareConfigurationForEditorEvent;

/**
 * Adds the "Variables" dropdown to every CKEditor 5 preset.
 */
#[AsEventListener('t3s-vars/rte-variables')]
final readonly class AddRteVariables
{
    private const MODULE = '@t3s/vars/ckeditor5-vars.js';
    private const TOOLBAR_ITEM = 't3sVars';
    private const LLL = 'LLL:EXT:t3s_vars/Resources/Private/Language/locallang.xlf:';

    public function __construct(
        private VariableService $variableService,
        private ExtensionConfiguration $extensionConfiguration,
    ) {}

    public function __invoke(AfterPrepareConfigurationForEditorEvent $event): void
    {
        if (!$this->isEnabled()) {
            $this->removeToolbarItem($event);
            return;
        }

        $site = $event->getData()['site'] ?? null;
        $labels = $this->variableService->getLabels($site);
        $items = [];
        $values = $this->variableService->getValues($site);
        // same order as in the content element "Address", custom keys at the end
        $order = array_flip(VariableService::ORDER);
        uksort($values, static fn(string $a, string $b): int => ($order[$a] ?? PHP_INT_MAX) <=> ($order[$b] ?? PHP_INT_MAX));
        foreach ($values as $key => $value) {
            if ($value !== '') {
                $items[] = ['key' => $key, 'label' => ($labels[$key] ?? $key) . ': ' . $value];
            }
        }
        if ($items === []) {
            $this->removeToolbarItem($event);
            return;
        }

        $configuration = $event->getConfiguration();
        $configuration['t3sVars'] = [
            'items' => $items,
            'label' => $this->translate('rte.label', 'Variables'),
            'tooltip' => $this->translate('rte.tooltip', 'Insert variable'),
        ];

        $modules = $configuration['importModules'] ?? [];
        if (!in_array(self::MODULE, array_map(static fn($m) => is_array($m) ? ($m['module'] ?? '') : $m, $modules), true)) {
            $modules[] = ['module' => self::MODULE, 'exports' => ['T3sVars']];
        }
        $configuration['importModules'] = $modules;

        // toolbar as "toolbar.items" or plain list; keep position if the preset already lists the item
        if (is_array($configuration['toolbar']['items'] ?? null)) {
            $configuration['toolbar']['items'] = $this->addToolbarItem($configuration['toolbar']['items']);
        } elseif (is_array($configuration['toolbar'] ?? null) && array_is_list($configuration['toolbar'])) {
            $configuration['toolbar'] = $this->addToolbarItem($configuration['toolbar']);
        }

        $event->setConfiguration($configuration);
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, mixed>
     */
    private function addToolbarItem(array $items): array
    {
        if (!in_array(self::TOOLBAR_ITEM, $items, true)) {
            $items[] = '|';
            $items[] = self::TOOLBAR_ITEM;
        }
        return $items;
    }

    // avoid CKEditor warning "toolbarview-item-unavailable" if a preset lists t3sVars
    private function removeToolbarItem(AfterPrepareConfigurationForEditorEvent $event): void
    {
        $configuration = $event->getConfiguration();
        $strip = static fn(array $items): array => array_values(array_filter($items, static fn($item) => $item !== self::TOOLBAR_ITEM));
        if (is_array($configuration['toolbar']['items'] ?? null)) {
            $configuration['toolbar']['items'] = $strip($configuration['toolbar']['items']);
        } elseif (is_array($configuration['toolbar'] ?? null) && array_is_list($configuration['toolbar'])) {
            $configuration['toolbar'] = $strip($configuration['toolbar']);
        } else {
            return;
        }
        $event->setConfiguration($configuration);
    }

    private function isEnabled(): bool
    {
        try {
            return (bool)$this->extensionConfiguration->get('t3s_vars', 'rteButton');
        } catch (\Throwable) {
            return true;
        }
    }

    private function translate(string $key, string $fallback): string
    {
        return $GLOBALS['LANG']?->sL(self::LLL . $key) ?: $fallback;
    }
}
