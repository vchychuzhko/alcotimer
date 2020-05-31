<?php

namespace Awesome\Frontend\Console;

use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Frontend\Model\StaticContent;

class StaticDeploy extends \Awesome\Console\Model\Cli\AbstractCommand
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
