<?php

declare(strict_types=1);

namespace T3S\T3sVars\UserFunc;

use Psr\Http\Message\ServerRequestInterface;
use T3S\T3sVars\Service\VariableService;
use TYPO3\CMS\Core\Attribute\AsAllowedCallable;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * postUserFunc for lib.parseFunc(_RTE).nonTypoTagStdWrap
 */
final class ParseFuncReplacer
{
    private ?ContentObjectRenderer $cObj = null;

    public function __construct(private readonly VariableService $variableService) {}

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }

    /**
     * @param array<string, mixed> $conf
     */
    #[AsAllowedCallable]
    public function replace(mixed $content, array $conf, ServerRequestInterface $request): string
    {
        return $this->variableService->replace((string)$content, $request->getAttribute('site'), $this->cObj, $request->getAttribute('language'));
    }
}
