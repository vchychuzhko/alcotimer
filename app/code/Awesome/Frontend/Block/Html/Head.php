<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Html;

use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\DeployedVersion;
use Awesome\Frontend\Model\FrontendState;

class Head extends \Awesome\Frontend\Block\Template
{
    private const HEAD_ADDITIONAL_BLOCK = 'head.additional';

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Frontend::html/head.phtml';

    /**
     * Head constructor.
     * @param DeployedVersion $deployedVersion
     * @param FrontendState $frontendState
     * @param array $data
     */
    public function __construct(DeployedVersion $deployedVersion, FrontendState $frontendState, array $data = [])
    {
        parent::__construct($deployedVersion, $data);
        $this->frontendState = $frontendState;
    }

    /**
     * Get page title, translating it.
     * @return string
     */
    public function getTitleHtml(): string
    {
        $titleHtml = '';

        if ($data = $this->getData('title')) {
            $title = $data['translate'] ? __($data['content']) : $data['content'];

            $titleHtml = <<<HTML
<title>$title</title>

HTML;
        }

        return $titleHtml;
    }

    /**
     * Get page description, translating it.
     * @return string
     */
    public function getDescriptionHtml(): string
    {
        $descriptionHtml = '';

        if ($data = $this->getData('description')) {
            $description = $data['translate'] ? __($data['content']) : $data['content'];

            $descriptionHtml = <<<HTML
<meta name="description" content="$description"/>

HTML;
        }

        return $descriptionHtml;
    }

    /**
     * Get page keywords, translating them one by one.
     * @return string
     */
    public function getKeywordsHtml(): string
    {
        $keywordsHtml = '';

        if ($data = $this->getData('keywords')) {
            $keywords = implode(', ', array_map(static function ($keyword) use ($data) {
                $keyword = trim($keyword);

                return $data['translate'] ? __($keyword) : $keyword;
            }, explode(',', $data['content'])));

            $keywordsHtml = <<<HTML
<meta name="keywords" content="$keywords"/>

HTML;
        }

        return $keywordsHtml;
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
            $cssMinified = $this->frontendState->isCssMinificationEnabled();
            $jsMinified = $this->frontendState->isJsMinificationEnabled();

            foreach ($preloads as $preload => $data) {
                switch ($as = $data['as']) {
                    case 'style':
                        $minified = $cssMinified;
                        break;
                    case 'script':
                        $minified = $jsMinified;
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
<link rel="stylesheet" type="text/css" href="$href"{$media}/>

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
        $headAdditionalContent = '';

        if ($layout = $this->getLayout()) {
            $headAdditionalContent = $layout->render(self::HEAD_ADDITIONAL_BLOCK);
        }

        return $headAdditionalContent;
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
