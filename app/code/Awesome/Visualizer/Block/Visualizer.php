<?php
declare(strict_types=1);

namespace Awesome\Visualizer\Block;

use Awesome\Framework\Model\Config;
use Awesome\Framework\Model\Serializer\Json;
use Awesome\Frontend\Model\Context;

class Visualizer extends \Awesome\Frontend\Block\Template
{
    private const TITLE_CONFIG_PATH = 'visualizer/title';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var Json $json
     */
    private $json;

    /**
     * Visualizer constructor.
     * @param Context $context
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Config $config,
        Context $context,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->json = $json;
    }

    /**
     * Get registered playlist configuration encoding it.
     * @return string
     */
    public function getPlaylistJsonConfig(): string
    {
        return $this->json->prettyEncode($this->getPlaylistConfig());
    }

    /**
     * Get visualizer page title.
     * @return string
     */
    public function getTitle(): string
    {
        return (string) $this->config->get(self::TITLE_CONFIG_PATH);
    }

    /**
     * Get registered playlist configuration.
     * @return array
     */
    public function getPlaylistConfig(): array
    {
        return [];
    }

    /**
     * Get default playlist item thumbnail URL.
     * @return string
     */
    public function getPlaylistDefaultThumbnail(): string
    {
        return $this->getMediaFileUrl('/pub/media/podcasts/thumbnails/default.png');
    }
}
