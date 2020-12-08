<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Validator;

class IpValidator implements \Awesome\Framework\Model\ValidatorInterface
{
    /**
     * Validate IP address.
     * @param string $item
     * @return bool
     */
    public function valid($item): bool
    {
        return (bool) filter_var($item, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
    }
}
