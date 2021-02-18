<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Css;

use tubalmartin\CssMin\Minifier as TubalMartinMinifier;

/**
 * Wrapper for TubalMartin css minifier.
 * @see \tubalmartin\CssMin\Minifier
 * @link https://github.com/tubalmartin/YUI-CSS-compressor-PHP-port
 */
class CssMinifier
{
    /**
     * @var TubalMartinMinifier $minifier
     */
    private $minifier;

    /**
     * CssMinifier constructor.
     * @param TubalMartinMinifier $minifier
     */
    public function __construct(TubalMartinMinifier $minifier)
    {
        $this->minifier = $minifier;
    }

    /**
     * Wrap TubalMartin minify method.
     * @param string $css
     * @return string
     */
    public function minify(string $css): string
    {
        return $this->minifier->run($css);
    }

    /**
     * Wrap TubalMartin keepSourceMapComment method.
     * @param bool $keepSourceMap
     * @return void
     */
    public function keepSourceMap(bool $keepSourceMap = true): void
    {
        $this->minifier->keepSourceMapComment($keepSourceMap);
    }
}
