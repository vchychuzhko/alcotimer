<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

interface ResponseInterface
{
    public const SUCCESS_STATUS_CODE = 200;
    public const FORBIDDEN_STATUS_CODE = 403;
    public const NOTFOUND_STATUS_CODE = 404;
    public const INTERNAL_ERROR_STATUS_CODE = 500;
    public const SERVICE_UNAVAILABLE_STATUS_CODE = 503;

    /**
     * Prepare and return response.
     * @return void
     */
    public function proceed(): void;
}
