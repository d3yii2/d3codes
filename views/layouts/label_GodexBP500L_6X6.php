<?php

use yii\web\View;
/**
 * @var View $this
 * @var string $content
 */



$this->beginPage()
?><!DOCTYPE html>
    <head>
        <meta charset="utf-8">
    <style type="text/css">
        html {width: 330px;}
        @page {
            /*    size: 5cm 5cm; */
            margin: 1mm 1mm 1mm 1mm;
        }
        @media print {
            body{
                width: 300px;
                text-align: center;
            }
        }
    </style>
        <?php $this->head(); ?>
    </head>
    <?php $this->beginBody() ?>
    <?=$content?>
    <?php $this->endBody() ?>
    </body>
</html>
<?php
$this->endPage();