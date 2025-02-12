<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\commerce\web\assets\commercecp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;
use yii\web\JqueryAsset;

/**
 * Asset bundle for the Control Panel
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class CommerceCpAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CpAsset::class,
            JqueryAsset::class,
        ];

        // These files are generated by gulp
        $this->css[] = 'css/commercecp.css';
        $this->js[] = 'commercecp.js';

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        if ($view instanceof View) {
            $view->registerTranslations('commerce', [
                'Add Address',
                'Add',
                'Address Line 1',
                'Address Line 2',
                'Address Updated.',
                'Alternative Phone',
                'Business Name',
                'Business Tax ID',
                'Cancel',
                'City',
                'Country',
                'Download Type',
                'Download',
                'Edit',
                'First Name',
                'Last Name',
                'Message',
                'New product',
                'New {productType} product',
                'New',
                'No Address',
                'PDF',
                'Phone (Alt)',
                'Phone',
                'State',
                'Status Updated.',
                'Status change message',
                'Update Address',
                'Update Order Status',
                'Update',
                'Update',
                'Zip Code',
            ]);
        }
    }
}
