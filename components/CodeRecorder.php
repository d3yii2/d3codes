<?php

namespace d3yii2\d3codes\components;

use d3yii2\d3codes\models\D3CodesCodeQueue;
use d3system\compnents\ModelsList;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3codes\dictionaries\D3CodesCodeDictionary;
use d3yii2\d3codes\models\D3CodesCodeRecord;
use Yii;
use yii\base\Component;


class CodeRecorder  extends Component {


    /** @var string */
    public $codeName;

    /** @var array */
    public $series;

    /** @var string */
    public $modelClass;
    
    /** @var int */
    public $modelId;

    /** @var Serries[] */
    private $seriesList = [];

    /** @var string */
    public $componentsSysModel;

    /** @var ModelsList */
    private $sysModel;

    /** @var int */
    private $codeId;

    /**
     * @throws D3ActiveRecordException
     */
    public function init(): void
    {
        $this->codeId = D3CodesCodeDictionary::getIdByName($this->codeName);
        $sysModelName = $this->componentsSysModel;
        $this->sysModel = Yii::$app->$sysModelName;
        $this->modelId = $this->sysModel->getIdByClassName($this->modelClass);
        foreach ($this->series as $name => $s){
            $this->seriesList[$name] = new Serries($name,$s['prefix'],$s['length'],$s['from'],$s['to']);
        }

    }

    /**
     * @param int $modelRecordId
     * @return bool|string
     * @throws D3ActiveRecordException
     */
    public function getCodeOrCreate(int $modelRecordId): string
    {
        if($code = $this->getBarCode($modelRecordId)
        ){
            return $code;
        }

        return $this->createNewRecord($modelRecordId);
    }

    public function getBarCode(int $modelRecordId)
    {
        return D3CodesCodeRecord::find()
            ->select('full_code')
            ->andWhere([
                'code_id' => $this->codeId,
                'model_id' => $this->modelId,
                'model_record_id' => $modelRecordId
            ])
            ->scalar();
    }

    public function addToQueue(int $modelRecordId): void
    {
        $id = D3CodesCodeRecord::find()
            ->select('id')
            ->andWhere([
                'code_id' => $this->codeId,
                'model_id' => $this->modelId,
                'model_record_id' => $modelRecordId
            ])
            ->scalar();
        $queue = new D3CodesCodeQueue();
        $queue->record_id = $id;
        $queue->save();
    }

    public function removeFromQueue(int $modelRecordId): void
    {
        $codeQueueIdList = D3CodesCodeQueue::find()
            ->select('d3codes_code_queue.id')
            ->innerJoin(
                'd3codes_code_record',
                'd3codes_code_record.id = d3codes_code_queue.record_id'
            )
            ->andWhere([
                'd3codes_code_record.code_id' => $this->codeId,
                'd3codes_code_record.model_id' => $this->modelId,
                'd3codes_code_record.model_record_id' => $modelRecordId
            ])
            ->column();
        D3CodesCodeQueue::deleteAll(['id' => $codeQueueIdList]);

    }

    public function countQueue(int $modelRecordId): int
    {
        return (int)D3CodesCodeRecord::find()
            ->select(['cnt'=>'IFNULL(COUNT(*),0)'])
            ->innerJoin(
                'd3codes_code_queue',
                'd3codes_code_record.id = d3codes_code_queue.record_id'
            )
            ->andWhere([
                'd3codes_code_record.code_id' => $this->codeId,
                'd3codes_code_record.model_id' => $this->modelId,
                'd3codes_code_record.model_record_id' => $modelRecordId
            ])
            ->scalar();
    }
    /**
     * @param int $modelRecordId
     * @return bool|string
     * @throws D3ActiveRecordException
     */
    public function createNewRecord(int $modelRecordId)
    {
        foreach ($this->seriesList as $series){
            $seriesId = $series->getSerriesId();
            $maxSqn = D3CodesCodeRecord::find()
                ->select('MAX(sqn)')
                ->where([
                    'code_id' => $this->codeId,
                    'model_id' => $this->modelId,
                    'series_id' => $seriesId
                ])
                ->scalar();
            $nextSqn = $series->getNextSqn($maxSqn);
            if($nextSqn !== false){
                $code = $series->createCode($nextSqn);
                $model = new D3CodesCodeRecord();
                $model->code_id = $this->codeId;
                $model->model_id = $this->modelId;
                $model->series_id = $seriesId;
                $model->model_record_id = $modelRecordId;
                $model->sqn = $nextSqn;
                $model->full_code = $code;
                if(!$model->save()){
                    throw new D3ActiveRecordException($model);
                }

                return $code;
            }
        }
        return false;
    }
}
