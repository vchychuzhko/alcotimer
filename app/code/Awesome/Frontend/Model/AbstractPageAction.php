<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Frontend\Model\Http\PageResponseFactory;

abstract class AbstractPageAction extends \Awesome\Framework\Model\AbstractAction
{
    protected const PAGE_LAYOUT = 'default';

    /**
     * AbstractPageAction constructor.
     * @param PageResponseFactory $pageResponseFactory
     */
    public function __construct(
        PageResponseFactory $pageResponseFactory
    ) {
        parent::__construct($pageResponseFactory);
    }
}
