<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\DeployedVersion;

class Context implements \Awesome\Framework\Model\SingletonInterface
{
    /**
     * @var DeployedVersion $deployedVersion
     */
    private $deployedVersion;

    /**
     * Template Context constructor.
     * @param DeployedVersion $deployedVersion
     */
    public function __construct(
        DeployedVersion $deployedVersion
    ) {
        $this->deployedVersion = $deployedVersion;
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
