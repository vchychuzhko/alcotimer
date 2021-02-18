<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Js;

use JShrink\Minifier as JShrinkMinifier;

/**
 * Wrapper for JShrink js minifier.
 * @see \JShrink\Minifier
 * @link https://github.com/tedious/JShrink
 */
class JsMinifier
{
    /**
     * Wrap JShrink minify method.
     * @param string $js
     * @return string
     */
    public function minify(string $js): string
    {
        return JShrinkMinifier::minify($js);
    }
}
