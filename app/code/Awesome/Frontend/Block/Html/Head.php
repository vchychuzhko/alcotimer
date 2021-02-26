<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Html;

use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\RequireJs;

/**
 * Class Head
 * @method string|null getDescription()
 * @method string|null getKeywords()
 * @method string|null getFavicon()
 */
class Head extends \Awesome\Frontend\Block\Template
{
    private const HEAD_ADDITIONAL_BLOCK = 'head.additional';

    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Frontend::html/head.phtml';

    /**
     * Get page title, translating it if possible.
     * @return string
     */
    public function getTitle()
    {
        return __($this->getData('title'));
    }

    /**
     * Get resources to be preloaded, resolving their paths.
     * @return array
     */
    public function getPreloads(): array
    {
        $preloads = [];
        $preloadData = $this->getData('preloads') ?: [];

        foreach ($preloadData as $preload => $data) {
            $type = $data['type'];

            switch ($type) {
                case 'style':
                    $minified = $this->frontendState->isCssMinificationEnabled();
                    break;
                case 'script':
                    $minified = $this->frontendState->isJsMinificationEnabled();
                    break;
                default:
                    $minified = false;
            }
            $preloads[] = [
                'type' => $type,
                'href' => $this->resolveAssetPath($preload, $minified),
            ];
        }

        return $preloads;
    }

    /**
     * Get styles, resolving their paths.
     * @return array
     */
    public function getStyles(): array
    {
        $styles = [];
        $stylesData = $this->getData('styles') ?: [];
        $minified = $this->frontendState->isCssMinificationEnabled();

        foreach ($stylesData as $style => $data) {
            $styles[] = [
                'src' => $this->resolveAssetPath($style, $minified),
            ];
        }

        return $styles;
    }

    /**
     * Get scripts, resolving their paths.
     * @return array
     */
    public function getScripts(): array
    {
        $scripts = [];
        $scriptsData = $this->getData('scripts') ?: [];
        $minified = $this->frontendState->isJsMinificationEnabled();

        foreach ($scriptsData as $script => $data) {
            $scripts[] = [
                'src'   => $this->resolveAssetPath($script, $minified),
                'async' => $data['async'] ?? false,
                'defer' => $data['defer'] ?? false,
            ];
        }

        return $scripts;
    }

    /**
     * Render additional head block.
     * @return string
     */
    public function getHeadAdditional(): string
    {
        $headAdditionalContent = '';

        if ($layout = $this->getLayout()) {
            $headAdditionalContent = $layout->render(self::HEAD_ADDITIONAL_BLOCK);
        }

        return $headAdditionalContent;
    }

    /**
     * Resolve static assets path including minification flag if needed.
     * @param string $path
     * @param bool $minified
     * @return string
     */
    private function resolveAssetPath(string $path, bool $minified = false): string
    {
        if ($minified) {
            $path = StaticContentHelper::addMinificationFlag($path);
        }

        return $this->getStaticUrl($path);
    }
}
