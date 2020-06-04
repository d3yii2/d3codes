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
        <?php $this->head(); ?>
    </head>
    <?php $this->beginBody() ?>
    <?=$content?>
    <?php $this->endBody() ?>
    </body>
</html>
<?php
$this->endPage();