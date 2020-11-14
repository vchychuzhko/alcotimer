<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Validator;

class IpValidator extends \Awesome\Framework\Model\AbstractValidator
{
    /**
     * @param string $item
     * @return bool
     */
    public function valid($item): bool
    {
        if (!$valid = filter_var($item, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            $this->invalid[] = $item;
        }

        return $valid;
    }
}
