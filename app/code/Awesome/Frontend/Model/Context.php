<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\StaticContent;

class Context implements \Awesome\Framework\Model\SingletonInterface
{
    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * Template Context constructor.
     * @param FrontendState $frontendState
     * @param StaticContent $staticContent
     */
    public function __construct(
        FrontendState $frontendState,
        StaticContent $staticContent
    ) {
        $this->frontendState = $frontendState;
        $this->staticContent = $staticContent;
    }

    /**
     * Get frontend state object.
     * @return FrontendState
     */
    public function getFrontendState(): FrontendState
    {
        return $this->frontendState;
    }

    /**
     * Get static content object.
     * @return StaticContent
     */
    public function getStaticContent(): StaticContent
    {
        return $this->staticContent;
    }
}
