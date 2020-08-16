<?php

namespace Awesome\Frontend\Block\Html;

class Head extends \Awesome\Frontend\Block\Template
{
    /**
     * @inheritDoc
     */
    protected $template = 'Awesome_Frontend::html/head.phtml';

    /**
     * Get js libs, resolving their paths.
     * @return array
     */
    public function getLibs()
    {
        $libs = $this->getData('lib') ?: [];

        foreach ($libs as $index => $lib) {
            $libs[$index] = $this->resolveAssetPath($lib, 'lib');
        }

        return $libs;
    }

    /**
     * Get scripts, resolving their paths.
     * @return array
     */
    public function getScripts()
    {
        $scripts = $this->getData('script') ?: [];

        foreach ($scripts as $index => $script) {
            $scripts[$index] = $this->resolveAssetPath($script, 'js');
        }

        return $scripts;
    }

    /**
     * Get styles, resolving their paths.
     * @return array
     */
    public function getStyles()
    {
        $styles = $this->getData('css') ?: [];

        foreach ($styles as $index => $style) {
            $styles[$index] = $this->resolveAssetPath($style, 'css');
        }

        return $styles;
    }

    /**
     * Resolve XML assets path.
     * @param string $path
     * @param string $type
     * @return string
     */
    private function resolveAssetPath($path, $type)
    {
        if (strpos($path, '//') === false) {
            @list($module, $file) = explode('::', $path);

            if (isset($file)) {
                $path = $module . '/' . $type . '/' . $file;
            }
            $path = $this->getStaticUrl($this->renderer->getView() . '/' . $path);
        }

        return $path;
    }
}
