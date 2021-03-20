<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Html;

use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\Context;
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
     * @param Context $context
     * @param FrontendState $frontendState
     * @param array $data
     */
    public function __construct(Context $context, FrontendState $frontendState, array $data = [])
    {
        parent::__construct($context, $data);
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
            $keywords = $data['content'];

            if ($data['translate']) {
                $keywords = implode(',', array_map(static function ($keyword) {
                    return __($keyword);
                }, explode(',', $keywords)));
            }

            $keywordsHtml = <<<HTML
<meta name="keywords" content="$keywords"/>

HTML;
        }

        return $keywordsHtml;
    }

    /**
     * Get page favicon.
     * @return string
     */
    public function getFaviconHtml(): string
    {
        $faviconHtml = '';

        if ($data = $this->getData('favicon')) {
            $href = $data['src'];
            $type = $data['type'] ? ' type="' . $data['type'] . '"' : '';

            $faviconHtml = <<<HTML
<link rel="shortcut icon" href="$href"{$type}/>

HTML;
        }
        // @todo: add type resolving

        return $faviconHtml;
    }

    /**
     * Get resources to be preloaded, resolving their paths.
     * @return string
     */
    public function getPreloadsHtml(): string
    {
        $preloadsHtml = '';
        $preloadData = $this->getData('preloads') ?: [];

        foreach ($preloadData as $preload => $data) {
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

        return $preloadsHtml;
    }

    /**
     * Get styles, resolving their paths.
     * @return string
     */
    public function getStylesHtml(): string
    {
        $stylesHtml = '';
        $stylesData = $this->getData('styles') ?: [];
        $minified = $this->frontendState->isCssMinificationEnabled();

        foreach ($stylesData as $style => $data) {
            $href = $this->resolveAssetPath($style, $minified);
            $media = $data['media'] ? ' media="' . $data['media'] . '"' : '';

            $stylesHtml .= <<<HTML
<link rel="stylesheet" type="text/css" href="$href"{$media}/>

HTML;
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
        $scriptsData = $this->getData('scripts') ?: [];
        $minified = $this->frontendState->isJsMinificationEnabled();

        foreach ($scriptsData as $script => $data) {
            $src = $this->resolveAssetPath($script, $minified);
            $async = $data['async'] ? ' async' : '';
            $defer = $data['defer'] ? ' defer' : '';

            $scriptsHtml .= <<<HTML
<script src="$src"{$async}{$defer}></script>

HTML;
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
