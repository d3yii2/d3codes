<?php

namespace d3yii2\d3codes\components;

use d3system\compnents\ModelsList;
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

    /** @var string */
    public $componentsSysModel;

    /** @var ModelsList */
    private $sysModel;


    /**
     * @throws D3ActiveRecordException
     */
    public function init(): void
    {
        $sysModelName = $this->componentsSysModel;
        $this->sysModel = Yii::$app->$sysModelName;
        foreach($this->modelClassList as $modelClass){
            $this->modelIdList[$this->sysModel->getIdByClassName($modelClass)] = $modelClass;
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
