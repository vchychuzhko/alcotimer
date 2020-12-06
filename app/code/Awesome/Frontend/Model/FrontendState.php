<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

class FrontendState extends \Awesome\Framework\Model\AppState
{
    private const CSS_MINIFY_CONFIG = 'web/css/minify';
    private const JS_MINIFY_CONFIG = 'web/js/minify';

    /**
     * @var bool $isCssMinificationEnabled
     */
    private $isCssMinificationEnabled;

    /**
     * @var bool $isJsMinificationEnabled
     */
    private $isJsMinificationEnabled;

    /**
     * Check if css minification is enabled.
     * @return bool
     */
    public function isCssMinificationEnabled(): bool
    {
        if ($this->isCssMinificationEnabled === null) {
            $this->isCssMinificationEnabled = (bool) $this->config->get(self::CSS_MINIFY_CONFIG);
        }

        return $this->isCssMinificationEnabled;
    }

    /**
     * Check if js minification is enabled.
     * @return bool
     */
    public function isJsMinificationEnabled(): bool
    {
        if ($this->isJsMinificationEnabled === null) {
            $this->isJsMinificationEnabled = (bool) $this->config->get(self::JS_MINIFY_CONFIG);
        }

        return $this->isJsMinificationEnabled;
    }
}
