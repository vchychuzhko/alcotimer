<?php
declare(strict_types=1);

namespace Awesome\Frontend\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Frontend\Model\StaticContent;

class StaticDeploy extends \Awesome\Console\Model\Cli\AbstractCommand
{
    /**
     * @var StaticContent $staticContent
     */
    private $staticContent;

    /**
     * StaticDeploy constructor.
     * @param StaticContent $staticContent
     */
    public function __construct(StaticContent $staticContent)
    {
        $this->staticContent = $staticContent;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Generate static files (assets)')
            ->addArgument('view', InputDefinition::ARGUMENT_OPTIONAL, 'Generate static only for a specified view');
    }

    /**
     * Generate static files.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output): void
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
