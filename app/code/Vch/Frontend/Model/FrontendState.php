<?php
declare(strict_types=1);

namespace Vch\Frontend\Model;

class FrontendState extends \Vch\Framework\Model\AppState
{
    private const CSS_MINIFY_CONFIG = 'web/css/minify';
    private const JS_MINIFY_CONFIG = 'web/js/minify';

    private const CSS_SYMLINK_CONFIG = 'web/css/symlink';
    private const JS_SYMLINK_CONFIG = 'web/js/symlink';

    private bool $isCssMinificationEnabled;

    private bool $isJsMinificationEnabled;

    private bool $useSymlinkForCss;

    private bool $useSymlinkForJs;

    /**
     * Check if css minification is enabled.
     * @return bool
     */
    public function isCssMinificationEnabled(): bool
    {
        if (!isset($this->isCssMinificationEnabled)) {
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
        if (!isset($this->isJsMinificationEnabled)) {
            $this->isJsMinificationEnabled = (bool) $this->config->get(self::JS_MINIFY_CONFIG);
        }

        return $this->isJsMinificationEnabled;
    }

    /**
     * Check if symbolic links should be used for css files.
     * Allowed only in developer mode and if minification is disabled.
     * @return bool
     */
    public function useSymlinkForCss(): bool
    {
        if (!isset($this->useSymlinkForCss)) {
            $this->useSymlinkForCss = $this->config->get(self::CSS_SYMLINK_CONFIG)
                && !$this->isCssMinificationEnabled()
                && $this->isDeveloperMode();
        }

        return $this->useSymlinkForCss;
    }

    /**
     * Check if symbolic links should be used for js files.
     * Allowed only in developer mode and if minification is disabled.
     * @return bool
     */
    public function useSymlinkForJs(): bool
    {
        if (!isset($this->useSymlinkForJs)) {
            $this->useSymlinkForJs = $this->config->get(self::JS_SYMLINK_CONFIG)
                && !$this->isJsMinificationEnabled()
                && $this->isDeveloperMode();
        }

        return $this->useSymlinkForJs;
    }
}
