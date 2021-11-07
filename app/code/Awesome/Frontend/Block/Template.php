<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Frontend\Model\Context;
use Awesome\Frontend\Model\DeployedVersion;

class Template extends \Awesome\Frontend\Model\AbstractBlock
{
    /**
     * @var DeployedVersion $deployedVersion
     */
    private $deployedVersion;

    /**
     * @var string $staticUrl
     */
    private $staticUrl = '';

    /**
     * Template constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($data);
        $this->deployedVersion = $context->getDeployedVersion();
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

        if ($this->staticUrl === '' && $layout = $this->getLayout()) {
            $view = $layout->getView();

            if (!$this->deployedVersion->getVersion()) {
                $this->deployedVersion->generateVersion();
            }

            $this->staticUrl = '/static/version' . $this->deployedVersion->getVersion() . '/' . $view . '/';
        }

        return $this->staticUrl . $file;
    }
}
