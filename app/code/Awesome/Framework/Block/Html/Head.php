<?php

namespace Awesome\Framework\Block\Html;

class Head extends \Awesome\Framework\Block\Template
{
    protected $template = 'Awesome_Framework::html/head.phtml';

    /**
     * Get js libs URLs, resolving their path.
     * @return array
     */
    public function getLibs()
    {
        $libs = $this->getData('libs');

        foreach ($libs as $index => $lib) {
            $libs[$index] = $this->resolveAssetsPath($lib, 'js');
        }

        return $libs;
    }

    /**
     * Get scripts URLs, resolving their path.
     * @return array
     */
    public function getScripts()
    {
        $scripts = $this->getData('scripts');

        foreach ($scripts as $index => $script) {
            $scripts[$index] = $this->resolveAssetsPath($script, 'js');
        }

        return $scripts;
    }

    /**
     * Get styles URLs, resolving their path.
     * @return array
     */
    public function getStyles()
    {
        $styles = $this->getData('styles');

        foreach ($styles as $index => $style) {
            $styles[$index] = $this->resolveAssetsPath($style, 'css');
        }

        return $styles;
    }

    /**
     * Parse XML assets path to a valid URL.
     * @param string $path
     * @param string $type
     * @return string
     */
    private function resolveAssetsPath($path, $type)
    {
        @list($module, $file) = explode('::', $path);

        if (isset($file)) {
            $path = $module . '/' . $type . '/' . $file;
        }

        return $this->view . '/' . $path;
    }
}
