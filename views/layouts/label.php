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
    <title></title>
    <!-- Normalize or reset CSS with your favorite library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css">

    <!-- Load paper.css for happy printing -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.4.1/paper.css">
        <?php $this->head(); ?>
    </head>

    <?php $this->beginBody() ?>
    <section class="sheet padding-10mm">
                <?=$content?>
    </section>
    <?php $this->endBody() ?>
    </body>
</html>
<?php
$this->endPage();