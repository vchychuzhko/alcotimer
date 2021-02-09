<?php
declare(strict_types=1);

namespace Awesome\Frontend\Console;

use Awesome\Console\Model\Cli\Input;
use Awesome\Console\Model\Cli\Input\InputDefinition;
use Awesome\Console\Model\Cli\Output;
use Awesome\Framework\Model\FileManager;
use Awesome\Framework\Model\Http;
use Awesome\Frontend\Model\Styles;

class StylesWatch extends \Awesome\Console\Model\AbstractCommand
{
    private const SOURCE_FOLDER_PATTERN = '/*/*/view/%s/web/css/source';
    private const FILE_VIEW_PATTERN = '/(.*)\/view\/(%s)\/(.*)$/';

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
            ->addOption('interval', 'i', InputDefinition::OPTION_OPTIONAL, 'Set watch interval in seconds (1 by default)')
            ->addArgument('view', InputDefinition::ARGUMENT_OPTIONAL, 'Watch only after provided view');
    }

    /**
     * Watch for less files changes and update styles.css file.
     * @inheritDoc
     */
    public function execute(Input $input, Output $output): void
    {
        $definedViews = [Http::FRONTEND_VIEW, Http::BACKEND_VIEW];
        $view = $input->getArgument('view');
        $interval = ((int) $input->getOption('interval') ?: 1);

        if ($view) {
            if (!in_array($view, $definedViews, true)) {
                $output->writeln('Provided view was not recognized.');
                $output->writeln();
                $output->writeln('Available views:');
                $output->writeln($output->colourText(implode(', ', $definedViews)), 2);

                throw new \InvalidArgumentException('Invalid view name is provided');
            }

            $views = [$view];
        } else {
            $views = $definedViews;
        }
        $lastUpdate = time();
        $sourceFolderPattern = APP_DIR . sprintf(self::SOURCE_FOLDER_PATTERN, '{' . Http::BASE_VIEW . ',' . implode(',', $views) . '}');

        $output->writeln('Use "Ctrl+C" to terminate.');
        $output->writeln('Watching...');

        while (true) {
            clearstatcache();
            $updated = false;
            $modifiedFile = null;

            foreach (glob($sourceFolderPattern, GLOB_BRACE) as $sourceFolder) {
                $files = $this->fileManager->scanDirectory($sourceFolder, true, 'less');

                foreach ($files as $file) {
                    if (filemtime($file) > $lastUpdate) {
                        $updated = true;
                        $modifiedFile = $file;
                        break 2;
                    }
                }
            }

            if ($updated && $modifiedFile) {
                $lastUpdate = time();
                $output->writeln(sprintf('File has been changed: "%s"', $modifiedFile));
                $fileView = preg_replace(
                    sprintf(self::FILE_VIEW_PATTERN, Http::FRONTEND_VIEW . '|' . Http::BACKEND_VIEW . '|' . Http::BASE_VIEW),
                    '$2',
                    $modifiedFile
                );

                if ($fileView === Http::BASE_VIEW) {
                    $modifiedViews = [Http::FRONTEND_VIEW, Http::BACKEND_VIEW];
                } else {
                    $modifiedViews = [$fileView];
                }

                try {
                    foreach ($modifiedViews as $modifiedView) {
                        $this->styles->generate($modifiedView);
                        $output->writeln($output->colourText(sprintf('Styles were regenerated for "%s" view', $modifiedView)));
                    }
                } catch (\Exception $e) {
                    $output->writeln($output->colourText($e->getMessage(), Output::RED));
                }

                $output->writeln('Watching...');
            }

            sleep($interval);
        }
    }
}
