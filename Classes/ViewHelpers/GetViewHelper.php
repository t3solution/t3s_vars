<?php

declare(strict_types=1);

namespace T3S\T3sVars\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use T3S\T3sVars\Service\VariableService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Outputs a single variable, linked if configured.
 *
 *   <vars:get key="phone" />
 *   <vars:get key="phone" link="0" />
 *   {vars:get(key: 'company')}
 */
final class GetViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('key', 'string', 'Variable key without "vars." prefix', true);
        $this->registerArgument('link', 'bool', 'Render tel/mail/url link if configured', false, true);
        $this->registerArgument('default', 'string', 'Fallback if the variable does not exist', false, '');
    }

    public function render(): string
    {
        $request = $this->getRequest();
        $site = $request?->getAttribute('site');
        $service = GeneralUtility::makeInstance(VariableService::class);
        $language = $request?->getAttribute('language');
        $values = $service->getValues($site, $language);
        $key = (string)$this->arguments['key'];

        if (!array_key_exists($key, $values)) {
            return htmlspecialchars((string)$this->arguments['default']);
        }

        $cObj = null;
        if ($this->arguments['link'] && $request !== null) {
            $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $cObj->setRequest($request);
            $cObj->start([], '');
        }
        return $service->render($key, $values[$key], $site, $cObj, $language);
    }

    private function getRequest(): ?ServerRequestInterface
    {
        $context = $this->renderingContext;
        if ($context !== null && $context->hasAttribute(ServerRequestInterface::class)) {
            return $context->getAttribute(ServerRequestInterface::class);
        }
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }
}
