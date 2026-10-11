<?php

declare(strict_types=1);

namespace T3S\T3sVars\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use T3S\T3sVars\Service\VariableService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Replaces {{key}} placeholders in the (escaped) child content, e.g. for headers.
 *
 *   <vars:replace>{data.header}</vars:replace>
 *   {data.header -> vars:replace()}
 *   <vars:replace link="0">{data.header}</vars:replace>
 */
final class ReplaceViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;
    protected $escapeChildren = true;

    public function initializeArguments(): void
    {
        $this->registerArgument('link', 'bool', 'Render tel/mail/url links if configured', false, true);
    }

    public function render(): string
    {
        $content = (string)$this->renderChildren();
        $request = $this->getRequest();

        $cObj = null;
        if ($this->arguments['link'] && $request !== null) {
            $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $cObj->setRequest($request);
            $cObj->start([], '');
        }

        return GeneralUtility::makeInstance(VariableService::class)
            ->replace($content, $request?->getAttribute('site'), $cObj);
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
