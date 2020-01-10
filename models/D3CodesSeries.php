<?php

namespace d3yii2\d3codes\models;

use d3yii2\d3codes\dictionaries\D3CodesSerriesDictionary;
use \d3yii2\d3codes\models\base\D3CodesSerries as BaseD3CodesSerries;

/**
 * This is the model class for table "d3codes_series".
 */
class D3CodesSeries extends BaseD3CodesSerries
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        D3CodesSerriesDictionary::clearCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        D3CodesSerriesDictionary::clearCache();
    }
}
