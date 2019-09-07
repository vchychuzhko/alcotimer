<?php

namespace Awesome\Base\Model;

use Awesome\Base\Model\App;

class FallbackResolver
{
    private const VIEW_PATTERN = '/view\/\w+\//';

    /**
     * @param string $path
     * @return string
     */
    public function resolve($path)
    {
        if (!file_exists(APP_DIR . $path)) {
            $path = preg_replace(self::VIEW_PATTERN, 'view/' . App::BASE_VIEW . '/', $path);
        }

        return $path;
    }
}
