<?php

namespace d3yii2\d3codes\models;

use \d3yii2\d3codes\models\base\D3CodesCodeRecord as BaseD3CodesCodeRecord;

/**
 * This is the model class for table "d3codes_code_record".
 */
class D3CodesCodeRecord extends BaseD3CodesCodeRecord
{
    public function rules()
    {
        return array_merge(parent::rules(),[
            [['code_id','full_code'], 'unique','targetAttribute' => ['code_id','full_code']]
        ]);
    }
}
