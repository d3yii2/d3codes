<?php

namespace d3yii2\d3codes\components;

use d3system\dictionaries\SysModelsDictionary;
use d3yii2\d3codes\models\D3CodesCodeQueue;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3codes\dictionaries\D3CodesCodeDictionary;
use d3yii2\d3codes\models\D3CodesCodeRecord;
use Yii;
use yii\base\Component;
use yii\db\Exception;
use yii\helpers\VarDumper;
use yii2d3\d3persons\components\D3User;


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

    /** @var int */
    private $codeId;

    /**
     * @throws D3ActiveRecordException
     */
    public function init(): void
    {
        $this->codeId = D3CodesCodeDictionary::getIdByName($this->codeName);
        $this->modelId = SysModelsDictionary::getIdByClassName($this->modelClass);
        foreach ($this->series as $name => $s){
            $this->seriesList[$name] = new Serries($name,$s['prefix'],$s['length'],$s['from'],$s['to']);
        }

    }

    /**
     * @param int|null $modelRecordId
     * @return bool|string
     * @throws \d3system\exceptions\D3ActiveRecordException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function getCodeOrCreate(?int $modelRecordId): ?string
    {
        if (!$modelRecordId) {
            return null;
        }
        if ($code = $this->getBarCode($modelRecordId)) {
            return $code;
        }

        return $this->createNewRecord($modelRecordId);
    }

    /**
     * assign code to other model record
     * @param int $newModelRecordId
     * @param string $code
     * @throws \d3system\exceptions\D3ActiveRecordException
     * @throws \yii\db\Exception
     */
    public function assignCodeToOtherRecord(int $newModelRecordId, string $code): void
    {
        if(!$codeRecord = D3CodesCodeRecord::findOne([
                'full_code' => $code,
                'code_id' => $this->codeId,
                'model_id' => $this->modelId,
//                'model_record_id' => $modelRecordId
            ])
        ){
            throw new Exception('Can not find code record for code: ' . $code);
        }
        $codeRecord->model_record_id = $newModelRecordId;
        if(!$codeRecord->save()){
            throw new D3ActiveRecordException($codeRecord);
        }

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

    /**
     * @param int $modelRecordId
     * @param string $code
     * @return bool|string
     * @throws \d3system\exceptions\D3ActiveRecordException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    public function registerCode(int $modelRecordId, string $code)
    {
        return $this->createNewRecord($modelRecordId,$code);
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
     * @param string $code
     * @return bool|string
     * @throws D3ActiveRecordException
     * @throws \yii\db\Exception
     * @throws \yii\base\Exception
     */
    public function createNewRecord(int $modelRecordId, string $code = '')
    {
        foreach ($this->seriesList as $series){
            $loopCnt = 0;

            while (true) {
                $loopCnt ++;
                if ($loopCnt > 10 ) {
                    throw new \yii\base\Exception(
                        'createNewRecord loopCnt exceeded' . PHP_EOL .
                        '$code = ' . $code . PHP_EOL .
                        '$this->seriesList = ' . VarDumper::dumpAsString( $this->seriesList) . PHP_EOL .
                        '$series = ' . VarDumper::dumpAsString( $series) . PHP_EOL
                    );
                }
                if ($code) {
                    if (!$number = $series->getCodeNumber($code)) {
                        Yii::error('if (!$number = $series->getCodeNumber($code)) {' . PHP_EOL .
                            '$code = ' . $code . PHP_EOL .
                            '$series = ' . VarDumper::dumpAsString( $series) . PHP_EOL);
                    }
                } else {
                    $number = $this->createNextCode($series);
                }

                if ($number) {
                    $code = $series->createCode($number);
                    $model = new D3CodesCodeRecord();
                    $model->code_id = $this->codeId;
                    $model->model_id = $this->modelId;
                    $model->series_id = $series->getSerriesId();
                    $model->model_record_id = $modelRecordId;
                    $model->sqn = $number;
                    $model->full_code = $code;
                    if (!$transaction = Yii::$app->db->beginTransaction()) {
                        throw new Exception('Can not initiate transaction');
                    }
                    try {
                        if (!$model->save()) {
                            throw new D3ActiveRecordException($model);
                        }
                        $transaction->commit();
                        return $code;
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        usleep(random_int(100, 400000));
                        Yii::error('Try create code. ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param int $step
     * @return bool|string
     * @throws D3ActiveRecordException
     */
    public function createFakeNewRecord(int $step)
    {
        foreach ($this->seriesList as $series) {
            if ($number = $this->createNextCode($series, $step)) {
                return $series->createCode($number);
            }
        }
        return false;
    }

    /**
     * @param string $code
     * @return bool|string
     */
    public function isValidSeries(string $code = ''): bool
    {
        foreach ($this->seriesList as $series){

            if($series->getCodeNumber($code)){
                return true;

            }
        }
        return false;
    }

    /**
     * @param Serries $series
     * @param int $step
     * @return bool|int
     * @throws D3ActiveRecordException
     */
    public function createNextCode(Serries $series, int $step = 1)
    {
        $seriesId = $series->getSerriesId();
        $maxSqn = D3CodesCodeRecord::find()
            ->select('MAX(sqn)')
            ->where([
                'code_id' => $this->codeId,
                'model_id' => $this->modelId,
                'series_id' => $seriesId
            ])
            ->scalar();
        return $series->getNextSqn($maxSqn, $step);
    }


}
