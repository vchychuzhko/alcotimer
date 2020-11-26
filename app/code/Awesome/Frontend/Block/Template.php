<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Config;
use Awesome\Frontend\Model\Context;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\StaticContent;

class Template extends \Awesome\Frontend\Model\AbstractBlock
{
    /**
     * @var Cache $cache
     */
    protected $cache;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var FrontendState $frontendState
     */
    protected $frontendState;

    /**
     * @var StaticContent $staticContent
     */
    protected $staticContent;

    /**
     * @var string $mediaUrl
     */
    protected $mediaUrl;

    /**
     * @var string $staticUrl
     */
    protected $staticUrl;

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
        $this->cache = $context->getCache();
        $this->config = $context->getConfig();
        $this->frontendState = $context->getFrontendState();
        $this->staticContent = $context->getStaticContent();
    }

    /**
     * Return URI path to for file in the media folder.
     * If file is not specified, return media folder URI path.
     * @param string $file
     * @return string
     */
    public function getMediaUrl(string $file = ''): string
    {
        $file = ltrim($file, '/');

        if ($this->mediaUrl === null) {
            $this->mediaUrl = $this->getPubUrl('media/');
        }

        return $this->mediaUrl . $file;
    }

    /**
     * Return URI path to for file in the media folder by its root-relative path.
     * @param string $file
     * @return string
     */
    public function getMediaFileUrl(string $file): string
    {
        $mediaRelativePath = preg_replace('/^(\/?(pub)?)?\/?media\//', '', $file);

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

        if ($this->staticUrl === null && $layout = $this->getLayout()) {
            $view = $layout->getView();

            if (!$deployedVersion = $this->staticContent->getDeployedVersion()) {
                $deployedVersion = $this->staticContent->deploy($view)
                    ->getDeployedVersion();
            }

            $this->staticUrl = $this->getPubUrl(
                'static/' . ($deployedVersion ? 'version' . $deployedVersion . '/' : '') . $view . '/'
            );
        }

        return $this->staticUrl . $file;
    }

    /**
     * Return URI path for file in the pub folder.
     * If file is not specified, return pub folder URI path.
     * @param string $file
     * @return string
     */
    private function getPubUrl(string $file = ''): string
    {
        return ($this->frontendState->isPubRoot() ? '' : '/pub') . '/' . ltrim($file, '/');
    }
}
