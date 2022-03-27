<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model\Page;

/**
 * Class PageConfig
 * @method string|null getTitle()
 * @method self setTitle(string $title)
 * @method string|null getDescription()
 * @method self setDescription(string $description)
 * @method string|null getKeywords()
 * @method self setKeywords(string $keywords)
 * @method self setRobots(string $robots)
 */
class PageConfig extends \Awesome\Framework\Model\DataObject
{
    public const INDEX_FOLLOW_ROBOTS     = 'index,follow';
    public const INDEX_NOFOLLOW_ROBOTS   = 'index,nofollow';
    public const NOINDEX_FOLLOW_ROBOTS   = 'noindex,follow';
    public const NOINDEX_NOFOLLOW_ROBOTS = 'noindex,nofollow';

    /**
     * Get page robots configuration, index-follow by default.
     * @return string
     */
    public function getRobots(): string
    {
        return $this->getData('robots') ?: self::INDEX_FOLLOW_ROBOTS;
    }
}
