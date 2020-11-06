<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block\Html;

use Awesome\Framework\Model\Invoker;
use Awesome\Frontend\Helper\StaticContentHelper;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\RequireJs;
use Awesome\Frontend\Model\TemplateRenderer;

class Head extends \Awesome\Frontend\Block\Template
{
    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Frontend::html/head.phtml';

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * Header constructor.
     * @param TemplateRenderer $renderer
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $children
     * @param array $data
     */
    public function __construct(
        TemplateRenderer $renderer,
        string $nameInLayout,
        ?string $template = null,
        array $children = [],
        array $data = []
    ) {
        parent::__construct($renderer, $nameInLayout, $template, $children, $data);
        $this->frontendState = Invoker::getInstance()->get(FrontendState::class);
    }

    /**
     * Get styles, resolving their paths.
     * @return array
     */
    public function getStyles(): array
    {
        $styles = [];
        $stylesData = $this->getData('css') ?: [];
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
        $scriptsData = $this->getData('script') ?: [];
        $minified = $this->frontendState->isJsMinificationEnabled();

        foreach ($scriptsData as $script => $data) {
            $scripts[] = [
                'src' => $this->resolveAssetPath($script, $minified),
                'async' => $data['async'] ?? false,
                'defer' => $data['defer'] ?? false,
            ];
        }

        return $scripts;
    }

    /**
     * Resolve XML assets path.
     * @param string $path
     * @param bool $minified
     * @return string
     */
    private function resolveAssetPath(string $path, bool $minified = false): string
    {
        if (strpos($path, '::') !== false) {
            [$module, $file] = explode('::', $path);
            $type = pathinfo($file, PATHINFO_EXTENSION);

            $path = $module . '/' . $type . '/' . $file;
        }
        if ($minified && $path !== RequireJs::RESULT_FILENAME) {
            StaticContentHelper::addMinificationFlag($path);
        }

        return $this->getStaticUrl($path);
    }
}
