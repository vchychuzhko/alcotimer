<?php

namespace Awesome\Frontend\Block\Html;

class Head extends \Awesome\Frontend\Block\Template
{
    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Frontend::html/head.phtml';

    /**
     * Get styles, resolving their paths.
     * @return array
     */
    public function getStyles()
    {
        $styles = [];
        $stylesData = $this->getData('css') ?: [];

        foreach ($stylesData as $style => $data) {
            $styles[] = [
                'src' => $this->resolveAssetPath($style),
            ];
        }

        return $styles;
    }

    /**
     * Get scripts, resolving their paths.
     * @return array
     */
    public function getScripts()
    {
        $scripts = [];
        $scriptsData = $this->getData('script') ?: [];

        foreach ($scriptsData as $script => $data) {
            $scripts[] = [
                'src' => $this->resolveAssetPath($script),
                'async' => $data['async'] ?? false,
                'defer' => $data['defer'] ?? false,
            ];
        }

        return $scripts;
    }

    /**
     * Resolve XML assets path.
     * @param string $path
     * @return string
     */
    private function resolveAssetPath($path)
    {
        if (strpos($path, '::') !== false) {
            list($module, $file) = explode('::', $path);
            $type = pathinfo($file, PATHINFO_EXTENSION);

            $path = $module . '/' . $type . '/' . $file;
        }

        return $this->getStaticUrl($this->renderer->getView() . '/' . $path);
    }
}
