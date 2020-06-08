<?php

use yii\web\View;
Use d3yii2\d3codes\assetbundles\NormalizeAsset;
NormalizeAsset::register($this);
/**
 * @var View $this
 * @var string $content
 */



$this->beginPage()
?><!DOCTYPE html>
    <head>
        <meta charset="utf-8">
    <style type="text/css">
        html {width: 570px;}
        @page {
            /*    size: 5cm 5cm; */
            margin: 1mm 1mm 1mm 1mm;
        }
        @media print {
            body{
                width: 559px;
                text-align: center;
            }
        }
        body {
            padding: 20px;
            font-size: 20px;
            font-weight: bold;
            font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
        }
    </style>
        <?php $this->head(); ?>
    </head>
    <?php $this->beginBody() ?>
<body>
    <?=$content?>
    <?php $this->endBody() ?>
    </body>
</html>
<?php
$this->endPage();