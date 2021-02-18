<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

class FrontendState extends \Awesome\Framework\Model\AppState
{
    private const CSS_MINIFY_CONFIG = 'web/css/minify';
    private const JS_MINIFY_CONFIG = 'web/js/minify';

    private const CSS_SYMLINK_CONFIG = 'web/css/symlink';
    private const JS_SYMLINK_CONFIG = 'web/js/symlink';

    /**
     * @var bool $isCssMinificationEnabled
     */
    private $isCssMinificationEnabled;

    /**
     * @var bool $isJsMinificationEnabled
     */
    private $isJsMinificationEnabled;

    /**
     * @var bool $useSymlinkForCss
     */
    private $useSymlinkForCss;

    /**
     * @var bool $useSymlinkForJs
     */
    private $useSymlinkForJs;

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

    /**
     * Check if symbolic links should be used for css files.
     * Allowed only in developer mode.
     * @return bool
     */
    public function useSymlinkForCss(): bool
    {
        if ($this->useSymlinkForCss === null) {
            $this->useSymlinkForCss = (bool) $this->config->get(self::CSS_SYMLINK_CONFIG) && $this->isDeveloperMode();
        }

        return $this->useSymlinkForCss;
    }

    /**
     * Check if symbolic links should be used for js files.
     * Allowed only in developer mode.
     * @return bool
     */
    public function useSymlinkForJs(): bool
    {
        if ($this->useSymlinkForJs === null) {
            $this->useSymlinkForJs = (bool) $this->config->get(self::JS_SYMLINK_CONFIG) && $this->isDeveloperMode();
        }

        return $this->useSymlinkForJs;
    }
}
