<?php

namespace d3yii2\d3codes\models;

use d3yii2\d3codes\dictionaries\D3CodesCodeDictionary;
use \d3yii2\d3codes\models\base\D3CodesCode as BaseD3CodesCode;

/**
 * This is the model class for table "d3codes_code".
 */
class D3CodesCode extends BaseD3CodesCode
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        D3CodesCodeDictionary::clearCache();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        D3CodesCodeDictionary::clearCache();
    }
}
