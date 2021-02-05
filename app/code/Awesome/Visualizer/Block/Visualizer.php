<?php
declare(strict_types=1);

namespace Awesome\Visualizer\Block;

use Awesome\Framework\Model\Serializer\Json;
use Awesome\Frontend\Model\Context;

class Visualizer extends \Awesome\Frontend\Block\Template
{
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
    public function __construct(Context $context, Json $json, array $data = [])
    {
        parent::__construct($context, $data);
        $this->json = $json;
    }

    /**
     * Get predefined playlist configuration.
     * @return string
     */
    public function getPlaylistConfig(): string
    {
        return $this->json->encode([]);
    }
}
