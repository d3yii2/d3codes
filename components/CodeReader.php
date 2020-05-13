<?php

namespace d3yii2\d3codes\components;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3codes\models\D3CodesCodeRecord;
use Yii;
use yii\base\Component;
use yii\db\ActiveRecord;


class CodeReader  extends Component {

    /** @var string[] */
    public $modelClassList;
    
    /** @var int[] */
    public $modelIdList = [];

    /**
     * @throws D3ActiveRecordException
     */
    public function init(): void
    {

        foreach($this->modelClassList as $modelClass){
            $this->modelIdList[SysModelsDictionary::getIdByClassName($modelClass)] = $modelClass;
        }
    }


    public function findModel(string $code)
    {
            if(!$model = D3CodesCodeRecord::find()
                ->where([
                    'model_id' => array_keys($this->modelIdList),
                    'full_code' => $code
                ])
                ->one()
            ){
                return false;
            }
            /** @var ActiveRecord $codeModelClass */
            $codeModelClass = $this->modelIdList[(int)$model->model_id];
            return $codeModelClass::findOne($model->model_record_id);

    }
}
