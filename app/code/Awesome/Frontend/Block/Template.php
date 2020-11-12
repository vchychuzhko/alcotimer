<?php
declare(strict_types=1);

namespace Awesome\Frontend\Block;

use Awesome\Framework\Model\Invoker;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\StaticContent;
use Awesome\Frontend\Model\TemplateRenderer;

class Template extends \Awesome\Framework\Model\DataObject
{
    /**
     * @var TemplateRenderer $renderer
     */
    protected $renderer;

    /**
     * @var string $nameInLayout
     */
    protected $nameInLayout;

    /**
     * @var string $template
     */
    protected $template;

    /**
     * @var array $children
     */
    protected $children;

    /**
     * @var string $mediaUrl
     */
    protected $mediaUrl;

    /**
     * @var string $staticUrl
     */
    protected $staticUrl;

    /**
     * @var FrontendState $frontendState
     */
    protected $frontendState;

    /**
     * @var StaticContent $staticContent
     */
    protected $staticContent;

    /**
     * Template constructor.
     * @param TemplateRenderer $renderer
     * @param string $nameInLayout
     * @param string|null $template
     * @param array $children
     * @param array $data
     */
    public function __construct(
        TemplateRenderer $renderer,
        string $nameInLayout,
        ?string $template = null,
        array $children = [],
        array $data = []
    ) {
        parent::__construct($data, true);
        $this->renderer = $renderer;
        $this->nameInLayout = $nameInLayout;
        $this->template = $template ?: $this->template;
        $this->children = $children;
        $this->frontendState = Invoker::getInstance()->get(FrontendState::class);
        $this->staticContent = Invoker::getInstance()->get(StaticContent::class);
    }

    /**
     * Render template.
     * @return string
     * @throws \Exception
     */
    public function toHtml(): string
    {
        return $this->renderer->renderElement($this);
    }

    /**
     * Get child element.
     * Return all children if no name is specified.
     * @param string $childName
     * @return string
     */
    public function getChildHtml(string $childName = ''): string
    {
        $childHtml = '';

        if ($childName) {
            if (in_array($childName, $this->children, true)) {
                $childHtml = $this->renderer->render($childName);
            }
        } else {
            foreach ($this->children as $child) {
                $childHtml .= $this->renderer->render($child);
            }
        }

        return $childHtml;
    }

    /**
     * Return URI path to for file in the media folder.
     * If file is not specified, return media folder URI path.
     * @param string $file
     * @return string
     */
    public function getMediaUrl(string $file = ''): string
    {
        $file = ltrim($file, '/');

        if ($this->mediaUrl === null) {
            $this->mediaUrl = $this->getPubUrl('media/');
        }

        return $this->mediaUrl . $file;
    }

    /**
     * Return URI path to for file in the media folder by its root-relative path.
     * @param string $file
     * @return string
     */
    public function getMediaFileUrl(string $file): string
    {
        $mediaRelativePath = preg_replace('/^(\/?(pub)?)?\/?media\//', '', $file);

        return $this->getMediaUrl($mediaRelativePath);
    }

    /**
     * Return URI path for file in the static folder.
     * If file is not specified, return static folder URI path.
     * @param string $file
     * @return string
     */
    public function getStaticUrl(string $file = ''): string
    {
        $file = ltrim($file, '/');

        if ($this->staticUrl === null) {
            $view = $this->renderer->getView();

            if (!$deployedVersion = $this->staticContent->getDeployedVersion()) {
                $deployedVersion = $this->staticContent->deploy($view)
                    ->getDeployedVersion();
            }

            $this->staticUrl = $this->getPubUrl(
                'static/' . ($deployedVersion ? 'version' . $deployedVersion . '/' : '') . $view . '/'
            );
        }

        return $this->staticUrl . $file;
    }

    /**
     * Return URI path for file in the pub folder.
     * If file is not specified, return pub folder URI path.
     * @param string $file
     * @return string
     */
    private function getPubUrl(string $file = ''): string
    {
        return ($this->frontendState->isPubRoot() ? '' : '/pub') . '/' . ltrim($file, '/');
    }

    /**
     * Get element name.
     * @return string
     */
    public function getNameInLayout(): string
    {
        return $this->nameInLayout;
    }

    /**
     * Get element template.
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }
}
