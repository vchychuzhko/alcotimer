<?php

namespace Awesome\Cache\Model;

class StaticCache
{
    public const DEPLOYED_VERSION_FILE = 'pub/static/deployed_version.txt';
    private const ASSET_PUB_TRIGGER = '{@pubDir}';
    private const FRONTEND_STATIC_PATH = '/pub/static/frontend/';
}
