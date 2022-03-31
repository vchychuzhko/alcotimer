<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Html;

use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\DeployedVersion;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\Layout;
use Awesome\Frontend\Model\Page\PageConfig;

class Head extends \Awesome\Frontend\Block\Template
{
    private const HEAD_ADDITIONAL_BLOCK = 'head.additional';

    protected ?string $template = 'Awesome_Frontend::html/head.phtml';

    private FrontendState $frontendState;

    private PageConfig $pageConfig;

    /**
     * Head constructor.
     * @param DeployedVersion $deployedVersion
     * @param FrontendState $frontendState
     * @param Layout $layout
     * @param PageConfig $pageConfig
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $data
     */
    public function __construct(
        DeployedVersion $deployedVersion,
        FrontendState $frontendState,
        Layout $layout,
        PageConfig $pageConfig,
        string $nameInLayout,
        ?string $template = null,
        array $data = []
    ) {
        parent::__construct($deployedVersion, $layout, $nameInLayout, $template, $data);
        $this->frontendState = $frontendState;
        $this->pageConfig = $pageConfig;
    }

    /**
     * Get page title.
     * @return string
     */
    public function getTitleHtml(): string
    {
        if ($title = $this->pageConfig->getTitle()) {
            return <<<HTML
<title>$title</title>

HTML;
        }

        return '';
    }

    /**
     * Get page description.
     * @return string
     */
    public function getDescriptionHtml(): string
    {
        if ($description = $this->pageConfig->getDescription()) {
            return <<<HTML
<meta name="description" content="$description"/>

HTML;
        }

        return '';
    }

    /**
     * Get page keywords.
     * @return string
     */
    public function getKeywordsHtml(): string
    {
        if ($keywords = $this->pageConfig->getKeywords()) {
            return <<<HTML
<meta name="keywords" content="$keywords"/>

HTML;
        }

        return '';
    }

    /**
     * Get page robots settings.
     * @return string
     */
    public function getRobotsHtml(): string
    {
        if ($robots = $this->pageConfig->getRobots()) {
            return <<<HTML
<meta name="robots" content="$robots"/>

HTML;
        }

        return '';
    }

    /**
     * Get page favicons.
     * @return string
     */
    public function getFaviconHtml(): string
    {
        $faviconHtml = '';
        $icons = $this->getData('favicon') ?? [];

        foreach ($icons as $icon) {
            $rel = $icon['rel'];
            $href = $icon['href'];
            $type = $icon['type'] ? ' type="' . $icon['type'] . '"' : '';
            $sizes = $icon['sizes'] ? ' sizes="' . $icon['sizes'] . '"' : '';

            $faviconHtml .= <<<HTML
<link rel="$rel" href="$href"{$type}{$sizes}/>

HTML;
        }

        return $faviconHtml;
    }

    /**
     * Get app manifest.
     * @return string
     */
    public function getManifestHtml(): string
    {
        $manifestHtml = '';

        if ($manifest = $this->getData('manifest')) {
            $href = $manifest['href'];

            $manifestHtml = <<<HTML
<link rel="manifest" href="$href">

HTML;
            if ($themeColor = $manifest['themeColor']) {
                $manifestHtml .= <<<HTML
<meta name="theme-color" content="$themeColor">

HTML;
            }
        }

        return $manifestHtml;
    }

    /**
     * Get resources to be preloaded, resolving their paths.
     * @return string
     */
    public function getPreloadsHtml(): string
    {
        $preloadsHtml = '';

        if ($preloads = $this->getData('preloads')) {
            foreach ($preloads as $preload => $data) {
                switch ($as = $data['as']) {
                    case 'style':
                        $minified = $this->frontendState->isCssMinificationEnabled();
                        break;
                    case 'script':
                        $minified = $this->frontendState->isJsMinificationEnabled();
                        break;
                    default:
                        $minified = false;
                }
                $href = $this->resolveAssetPath($preload, $minified);
                $type = $data['type'] ? ' type="' . $data['type'] . '"' : '';

                $preloadsHtml .= <<<HTML
<link rel="preload" href="$href" as="$as"{$type} crossorigin="anonymous"/>

HTML;
            }
        }

        return $preloadsHtml;
    }

    /**
     * Get styles, resolving their paths.
     * @return string
     */
    public function getStylesHtml(): string
    {
        $stylesHtml = '';

        if ($styles = $this->getData('styles')) {
            $minified = $this->frontendState->isCssMinificationEnabled();

            foreach ($styles as $style => $data) {
                $href = $this->resolveAssetPath($style, $minified);
                $media = $data['media'] ? ' media="' . $data['media'] . '"' : '';

                $stylesHtml .= <<<HTML
<link rel="stylesheet" href="$href"{$media}/>

HTML;
            }
        }

        return $stylesHtml;
    }

    /**
     * Get scripts, resolving their paths.
     * @return string
     */
    public function getScriptsHtml(): string
    {
        $scriptsHtml = '';

        if ($scripts = $this->getData('scripts')) {
            $minified = $this->frontendState->isJsMinificationEnabled();

            foreach ($scripts as $script => $data) {
                $src = $this->resolveAssetPath($script, $minified);
                $async = $data['async'] ? ' async' : '';
                $defer = $data['defer'] ? ' defer' : '';

                $scriptsHtml .= <<<HTML
<script src="$src"{$async}{$defer}></script>

HTML;
            }
        }

        return $scriptsHtml;
    }

    /**
     * Render additional head block.
     * @return string
     */
    public function getHeadAdditional(): string
    {
        return $this->layout->render(self::HEAD_ADDITIONAL_BLOCK);
    }

    /**
     * Resolve static assets path including minification flag if needed.
     * Absolute paths remain unaffected.
     * @param string $path
     * @param bool $minified
     * @return string
     */
    private function resolveAssetPath(string $path, bool $minified = false): string
    {
        if (!preg_match('/^(https?:)?\/\//i', $path)) {
            $path = $this->getStaticUrl($path);

            if ($minified) {
                $path = StaticContentHelper::addMinificationFlag($path);
            }
        }

        return $path;
    }
}
