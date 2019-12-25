<?php

namespace Awesome\Framework\Block\Html;

class Head extends \Awesome\Framework\Block\Template
{
    protected $template = 'Awesome_Framework::html/head.phtml';

    /**
     * Get page title.
     * @return string
     */
    public function getTitle()
    {
        return (string) $this->getStructureData('title');
    }

    /**
     * Get page meta description.
     * @return string
     */
    public function getDescription()
    {
        return (string) $this->getStructureData('description');
    }

    /**
     * Get page meta keywords.
     * @return string
     */
    public function getKeywords()
    {
        return (string) $this->getStructureData('keywords');
    }

    /**
     * Get favicon src path.
     * @return string
     */
    public function getFaviconSrc()
    {
        return (string) $this->getStructureData('favicon');
    }

    /**
     * Get js libs, resolving their paths.
     * @return array
     */
    public function getLibs()
    {
        $libs = $this->getStructureData('lib') ?? [];

        foreach ($libs as $index => $lib) {
            $libs[$index] = $this->resolveAssetsPath($lib, 'js');
        }

        return $libs;
    }

    /**
     * Get scripts, resolving their paths.
     * @return array
     */
    public function getScripts()
    {
        $scripts = $this->getStructureData('script') ?? [];

        foreach ($scripts as $index => $script) {
            $scripts[$index] = $this->resolveAssetsPath($script, 'js');
        }

        return $scripts;
    }

    /**
     * Get styles, resolving their paths.
     * @return array
     */
    public function getStyles()
    {
        $styles = $this->getStructureData('css') ?? [];

        foreach ($styles as $index => $style) {
            $styles[$index] = $this->resolveAssetsPath($style, 'css');
        }

        return $styles;
    }

    /**
     * Resolve XML assets path.
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
