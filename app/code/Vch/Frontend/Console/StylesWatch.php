<?php
declare(strict_types=1);

namespace Vch\Frontend\Console;

use Vch\Console\Model\Cli\Input;
use Vch\Console\Model\Cli\Input\InputDefinition;
use Vch\Console\Model\Cli\Output;
use Vch\Framework\Model\FileManager;
use Vch\Framework\Model\Http;
use Vch\Frontend\Model\Generator\Styles;

class StylesWatch extends \Vch\Console\Model\AbstractCommand
{
    private const SOURCE_FOLDER_PATTERN = '/*/*/view/%s/web/css/source';
    private const FILE_VIEW_PATTERN = '/(.*)\/view\/(%s)\/(.*)$/';

    private const DEFAULT_WATCH_INTERVAL = 1;

    /**
     * @var FileManager $fileManager
     */
    private $fileManager;

    /**
     * @var Styles $styles
     */
    private $styles;

    /**
     * StylesWatch constructor.
     * @param FileManager $fileManager
     * @param Styles $styles
     */
    public function __construct(FileManager $fileManager, Styles $styles)
    {
        $this->fileManager = $fileManager;
        $this->styles = $styles;
    }

    /**
     * @inheritDoc
     */
    public static function configure(): InputDefinition
    {
        return parent::configure()
            ->setDescription('Watch after less files modification')
            ->addOption('interval', null, InputDefinition::OPTION_OPTIONAL, sprintf(
                'Set watch interval in seconds (default: %s)', self::DEFAULT_WATCH_INTERVAL
            ))
            ->addArgument('view', InputDefinition::ARGUMENT_OPTIONAL, 'Watch only after provided view');
    }

    /**
     * Watch for less files changes and update styles.css file.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output)
    {
        $views = Http::getAllViews();
        $requestedView = $input->getArgument('view');

        if (!is_null($requestedView)) {
            if (!in_array($requestedView, $views, true)) {
                $output->writeln('Provided view is not registered.');
                $output->writeln();
                $output->writeln('Available views:');
                $output->writeln($output->colourText(implode(', ', $views)), 2);

                throw new \InvalidArgumentException('Invalid view name is provided');
            }

            $views = [$requestedView];
        }

        $interval = ((int) $input->getOption('interval') ?: self::DEFAULT_WATCH_INTERVAL);
        $lastUpdate = time();

        $watchingLabel = $requestedView
            ? __('Watching after %1 view...', $output->underline($requestedView))
            : __('Watching...');

        $output->writeln(__('Use "%1" to terminate.', 'Ctrl+C'));
        $output->writeln($watchingLabel);

        $sourceFolderPattern = APP_DIR . sprintf(self::SOURCE_FOLDER_PATTERN, '{' . Http::BASE_VIEW . ',' . implode(',', $views) . '}');
        $fileViewPattern = sprintf(self::FILE_VIEW_PATTERN, Http::FRONTEND_VIEW . '|' . Http::BACKEND_VIEW . '|' . Http::BASE_VIEW);

        while (true) {
            clearstatcache();
            $modified = [];

            foreach (glob($sourceFolderPattern, GLOB_BRACE) as $sourceFolder) {
                $files = $this->fileManager->scanDirectory($sourceFolder, true, 'less');

                foreach ($files as $file) {
                    if (filemtime($file) > $lastUpdate) {
                        $output->writeln(__('File has been modified: "%1"', $file));

                        preg_match($fileViewPattern, $file, $matches);
                        $modified[] = $matches[2];
                    }
                }
            }

            if ($modified) {
                $lastUpdate = time();

                if (in_array(Http::BASE_VIEW, $modified, true)) {
                    $modified = $views;
                } else {
                    $modified = array_unique($modified);
                }

                try {
                    foreach ($modified as $view) {
                        $this->styles->generate(Styles::RESULT_FILENAME, $view);

                        $output->writeln(
                            $output->colourText(__('Styles were regenerated for %1 view', $output->underline($view)))
                        );
                    }
                } catch (\Exception $e) {
                    $output->writeln($output->colourText($e->getMessage(), Output::RED));
                }

                $output->writeln($watchingLabel);
            }

            sleep($interval);
        }
    }
}
