<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

interface ResponseInterface
{
    public const SUCCESS_STATUS_CODE = 200;
    public const UNAUTHORIZED_STATUS_CODE = 401;
    public const FORBIDDEN_STATUS_CODE = 403;
    public const NOTFOUND_STATUS_CODE = 404;
    public const INTERNALERROR_STATUS_CODE = 500;
    public const SERVICEUNAVAILABLE_STATUS_CODE = 503;

    /**
     * Prepare and return response.
     * @return void
     */
    public function proceed(): void;
}
