<?php

namespace d3yii2\d3codes\components;

use d3system\dictionaries\SysModelsDictionary;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3codes\models\D3CodesCodeRecord;
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

    /**
     * find code model record
     *
     * @param string $code
     * @return false|ActiveRecord|null
     */
    public function findModel(string $code)
    {
            if(!$model = $this->findCodeRecord(trim($code))){
                return false;
            }
            /** @var ActiveRecord $codeModelClass */
            $codeModelClass = $this->modelIdList[(int)$model->model_id];
            return $codeModelClass::findOne($model->model_record_id);

    }

    /**
     * @param string $code
     * @return array|D3CodesCodeRecord|ActiveRecord|null
     */
    public function findCodeRecord(string $code)
    {
        return D3CodesCodeRecord::find()
            ->where([
                'model_id' => array_keys($this->modelIdList),
                'full_code' => $code
            ])
            ->one();
    }
}
