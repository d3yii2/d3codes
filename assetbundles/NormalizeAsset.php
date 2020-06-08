<?php

namespace d3yii2\d3codes\assetbundles;

use yii\web\AssetBundle;

class NormalizeAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public function init()
    {
        parent::init();
        $this->sourcePath = \Yii::getAlias('@blankon_theme').'/assets';
    }

    public $css = [
        'th-modal.css',
    ];

    public $js = [
        'th-modal.js',
    ];
}
