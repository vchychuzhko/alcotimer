<?php
declare(strict_types=1);

namespace Vch\Frontend\Block;

use Vch\Frontend\Model\DeployedVersion;
use Vch\Frontend\Model\Layout;

class Template extends \Vch\Frontend\Model\AbstractBlock
{
    private DeployedVersion $deployedVersion;

    private string $staticUrl = '';

    /**
     * Template constructor.
     * @param DeployedVersion $deployedVersion
     * @param Layout $layout
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $data
     */
    public function __construct(
        DeployedVersion $deployedVersion,
        Layout $layout,
        string $nameInLayout,
        ?string $template = null,
        array $data = []
    ) {
        parent::__construct($layout, $nameInLayout, $template, $data);
        $this->deployedVersion = $deployedVersion;
    }

    /**
     * Return URI path to for file in the media folder.
     * If file is not specified, return media folder URI path.
     * @param string $file
     * @return string
     */
    public function getMediaUrl(string $file = ''): string
    {
        return '/media/' . ltrim($file, '/');
    }

    /**
     * Return URI path to for file in the media folder by its root-relative path.
     * @param string $file
     * @return string
     */
    public function getMediaFileUrl(string $file): string
    {
        $mediaRelativePath = preg_replace('/^\/?((pub\/)?|)media\//', '', $file);

        return $this->getMediaUrl($mediaRelativePath);
    }

    /**
     * Return URI path for file in the static folder.
     * If file is not specified, return static folder URI path.
     * @param string $file
     * @return string
     */
    public function getStaticUrl(string $file = ''): string
    {
        $file = ltrim($file, '/');

        if ($this->staticUrl === '') {
            $view = $this->layout->getView();

            if (!$this->deployedVersion->getVersion()) {
                $this->deployedVersion->generateVersion();
            }

            $this->staticUrl = '/static/version' . $this->deployedVersion->getVersion() . '/' . $view . '/';
        }

        return $this->staticUrl . $file;
    }
}
