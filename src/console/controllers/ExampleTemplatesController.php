<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\console\controllers;

use Craft;
use craft\commerce\console\Controller;
use craft\commerce\Plugin;
use craft\helpers\Console;
use craft\helpers\FileHelper;
use craft\helpers\Install as InstallHelper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use yii\console\ExitCode;

/**
 * Allows you to create a new database backup.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.1.21
 */
class ExampleTemplatesController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'generate';

    /**
     * @var string Name of the folder the templates will copy into
     * @since 3.x
     */
    public $folderName;

    /**
     * @var bool Whether to overwrite an existing folder. Must be passed if a folder with that name already exists.
     * @since 3.x
     */
    public $overwrite = false;

    /**
     * @var string The type of templates you want to generate. 'pro' for full templates or 'lite' for minimal templates.
     */
    public $templatesType = 'pro';

    /**
     * @var string The type of templates you want to generate. 'pro' for full templates or 'lite' for minimal templates.
     * Possible values are: blue, red
     */
    public $baseTailwindColor;

    /**
     * @var array
     */
    private $_replacementData = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        $options = parent::options($actionID);
        $options[] = 'folderName';
        $options[] = 'overwrite';
        $options[] = 'templatesType';
        $options[] = 'baseTailwindColor';
        return $options;
    }

    /**
     * Copy the example templates into
     *
     *
     *
     * @return int
     */
    public function actionGenerate(): int
    {

        // Example templates to use
        $exampleTemplates = Craft::$app->getPath()->getVendorPath() . DIRECTORY_SEPARATOR . 'craftcms' . DIRECTORY_SEPARATOR . 'commerce' . DIRECTORY_SEPARATOR . 'example-templates' . DIRECTORY_SEPARATOR . 'src';

        $sourceFolder = 'shop'; // As defaulted by the `templatesType` of 'pro'
        if ($this->templatesType == 'lite') {
            $sourceFolder = 'buy'; // As defaulted by the `templatesType` of 'lite'
        }

        $source = $exampleTemplates . DIRECTORY_SEPARATOR . $sourceFolder;


        $folderName = $this->folderName ?: $this->prompt('Folder name:', ['required' => true, 'default' => $sourceFolder]);
        if (!$folderName) {
            $errors[] = 'No destination folder name provided.';
            $this->stderr('Invalid arguments:' . PHP_EOL . '    - ' . implode(PHP_EOL . '    - ', $errors) . PHP_EOL, Console::FG_RED);
            return ExitCode::USAGE;
        }

        $colors = ['gray', 'red', 'blue', 'yellow', 'green', 'indigo', 'purple', 'pink'];

        $mainColor = $this->baseTailwindColor ?: $this->select('Base Tailwind CSS color:', array_combine($colors, $colors));
        $dangerColor = ($mainColor == 'red') ? 'purple' : 'red';

        $this->_replacementData = [
            '[[folderName]]' => $folderName,
            '[[color]]' => $mainColor,
            '[[classes.a]]' => "text-$mainColor-500 hover:text-$mainColor-600",
            '[[classes.input]]' => "border border-gray-300 hover:border-gray-500 px-4 py-2 leading-tight rounded",
            '[[classes.box.base]]' => "bg-gray-100 border-$mainColor-300 border-b-2 p-6",
            '[[classes.box.error]]' => "bg-$dangerColor-100 border-$dangerColor-500 border-b-2 p-6",
            '[[classes.btn.base]]' => "cursor-pointer rounded px-4 py-2 inline-block",
            '[[classes.btn.small]]' => "cursor-pointer rounded px-2 py-1 text-sm inline-block",
            '[[classes.btn.mainColor]]' => "bg-$mainColor-500 hover:bg-$mainColor-600 text-white hover:text-white",
            '[[classes.btn.grayColor]]' => "bg-gray-500 hover:bg-gray-600 text-white hover:text-white",
            '[[classes.btn.grayLightColor]]' => "bg-gray-300 hover:bg-gray-400 text-gray-600 hover:text-white"
        ];

        $this->stdout('Attempting to copy example templates ... ' . PHP_EOL);

        $pathService = Craft::$app->getPath();
        $tempDestination = $pathService->getTempPath() . DIRECTORY_SEPARATOR . 'commerce_example_templates_' . md5(uniqid(mt_rand(), true));
        FileHelper::copyDirectory($source, $tempDestination, ['recursive' => true, 'copyEmptyDirectories' => true]);

        $this->_replaceInAll($tempDestination, 'blue', 'red');

        $source = $tempDestination;

        // Validate the arguments
        $errors = [];

        $originalMode = Craft::$app->getView()->getTemplateMode();
        Craft::$app->getView()->setTemplateMode(\craft\web\View::TEMPLATE_MODE_SITE);
        $templatesPath = Craft::$app->getView()->getTemplatesPath();
        Craft::$app->getView()->setTemplateMode($originalMode);

        if (!$templatesPath) {
            $errors[] = 'Can not determine the site template path. [100]';
        }

        if ($templatesPath && !FileHelper::isWritable($templatesPath)) {
            $errors[] = 'Site template path is not writable. [101]';
        }

        if (!empty($errors)) {
            $this->stderr('Invalid arguments:' . PHP_EOL . '    - ' . implode(PHP_EOL . '    - ', $errors) . PHP_EOL, Console::FG_RED);
            return ExitCode::USAGE;
        }

        $errors = [];

        $destination = $templatesPath . DIRECTORY_SEPARATOR . $folderName;
        $alreadyExists = is_dir($destination);
        if ($alreadyExists && !$this->overwrite) {
            $errors[] = 'Folder with name "' . $folderName . '" already exists in the templates folder, and the `overwrite` param was not set to true to replace. [102]';
            $this->stderr('Errors:' . PHP_EOL . '    - ' . implode(PHP_EOL . '    - ', $errors) . PHP_EOL, Console::FG_RED);
            return ExitCode::USAGE;
        }

        $this->stdout('Source: ' . $source . PHP_EOL, Console::FG_GREY);
        $this->stdout('Destination: ' . $destination . PHP_EOL, Console::FG_GREY);

        if (is_dir($destination) && is_dir($source)) {
            if ($this->overwrite) {
                $this->stdout('Overwriting ...' . PHP_EOL, Console::FG_YELLOW);
                FileHelper::removeDirectory($destination);
            }
        }
        if (!is_dir($destination) && is_dir($source)) {
            try {
                $this->stdout('Copying ...' . PHP_EOL, Console::FG_YELLOW);
                FileHelper::copyDirectory($source, $destination, ['recursive' => true, 'copyEmptyDirectories' => true]);
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            $this->stderr('Errors:' . PHP_EOL . '    - ' . implode(PHP_EOL . '    - ', $errors) . PHP_EOL, Console::FG_RED);
            return ExitCode::USAGE;
        }

        $this->stdout('Done!' . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }

    /**
     * @param $dir
     * @param $find
     * @param $replace
     */
    private function _replaceInAll($dir, $find, $replace)
    {
        $files = FileHelper::findFiles($dir, [
            'only' => ['*.twig', '*.html', '*.svg']
        ]);


        foreach ($files as $file) {
            $fileContents = file_get_contents($file);
            $fileContents = str_replace(array_keys($this->_replacementData), array_values($this->_replacementData), $fileContents);
            file_put_contents($file, $fileContents);
        }
    }
}