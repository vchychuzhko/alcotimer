<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\DeployedVersion;
use Awesome\Frontend\Model\FrontendState;

class Context implements \Awesome\Framework\Model\SingletonInterface
{
    /**
     * @var DeployedVersion $deployedVersion
     */
    private $deployedVersion;

    /**
     * @var FrontendState $frontendState
     */
    private $frontendState;

    /**
     * Template Context constructor.
     * @param DeployedVersion $deployedVersion
     * @param FrontendState $frontendState
     */
    public function __construct(
        DeployedVersion $deployedVersion,
        FrontendState $frontendState
    ) {
        $this->deployedVersion = $deployedVersion;
        $this->frontendState = $frontendState;
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
     * Get deployed version object.
     * @return DeployedVersion
     */
    public function getDeployedVersion(): DeployedVersion
    {
        return $this->deployedVersion;
    }
}
