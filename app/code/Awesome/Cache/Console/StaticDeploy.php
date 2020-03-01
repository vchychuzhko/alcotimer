<?php

namespace Awesome\Cache\Console;

use Awesome\Cache\Model\StaticContent;
use Awesome\Framework\Model\Cli\Input\InputDefinition;

class StaticDeploy extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * StaticDeploy constructor.
     */
    public function __construct()
    {
        $this->staticContent = new StaticContent();
    }

    /**
     * @inheritDoc
     */
    public static function configure($definition)
    {
        return parent::configure($definition)
            ->setDescription('Generate static files (assets)')
            ->addArgument('view', InputDefinition::ARGUMENT_OPTIONAL, 'Generate static only for a specified view');
    }

    /**
     * Generate static files.
     * @inheritDoc
     */
    public function execute($input, $output)
    {
        $view = $input->getArgument('view') ?: '';
        $this->staticContent->deploy($view);

        if ($view) {
            $output->writeln(sprintf('Static content was deployed for "%s" view.', $view));
        } else {
            $output->writeln('Static content was deployed for all views.');
        }
    }
}
