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
use yii\db\IntegrityException;

class CodeRecorder  extends Component {


    /** @var string */
    public $codeName;

    /** @var array */
    public $series;

    /** @var string|null */
    public $modelClass;

    /** @var int|null */
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
        if ($this->modelClass !== null) {
            $this->modelId = SysModelsDictionary::getIdByClassName($this->modelClass);
        }
        foreach ($this->series as $name => $s){
            $this->seriesList[$name] = new Serries($name,$s['prefix'],$s['length'],$s['from'],$s['to']);
        }

    }

    /**
     * @param int|null $modelRecordId
     * @return bool|string
     * @throws D3ActiveRecordException
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function getCodeOrCreate(?int $modelRecordId, ?string $modelClass = null): ?string
    {
        if (!$modelRecordId) {
            return null;
        }
        if ($code = $this->getBarCode($modelRecordId, $modelClass)) {
            return $code;
        }

        return $this->createNewRecord($modelRecordId, '', $modelClass);
    }

    /**
     * assign code to other model record
     * @param int $newModelRecordId
     * @param string $code
     * @throws D3ActiveRecordException
     * @throws Exception
     */
    public function assignCodeToOtherRecord(int $newModelRecordId, string $code, ?string $modelClass = null): void
    {
        $modelId = $this->resolveModelId($modelClass);
        if(!$codeRecord = D3CodesCodeRecord::findOne([
                'full_code' => $code,
                'code_id' => $this->codeId,
                'model_id' => $modelId,
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

    public function getBarCode(int $modelRecordId, ?string $modelClass = null)
    {
        $modelId = $this->resolveModelId($modelClass);
        return D3CodesCodeRecord::find()
            ->select('full_code')
            ->andWhere([
                'code_id' => $this->codeId,
                'model_id' => $modelId,
                'model_record_id' => $modelRecordId
            ])
            ->scalar();
    }

    /**
     * @param int $modelRecordId
     * @param string $code
     * @return bool|string
     * @throws D3ActiveRecordException
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function registerCode(int $modelRecordId, string $code, ?string $modelClass = null)
    {
        return $this->createNewRecord($modelRecordId, $code, $modelClass);
    }


    /**
     * @throws Exception
     */
    public function addToQueue(int $modelRecordId, ?string $modelClass = null): void
    {
        $modelId = $this->resolveModelId($modelClass);
        $id = D3CodesCodeRecord::find()
            ->select('id')
            ->andWhere([
                'code_id' => $this->codeId,
                'model_id' => $modelId,
                'model_record_id' => $modelRecordId
            ])
            ->scalar();
        $queue = new D3CodesCodeQueue();
        $queue->record_id = $id;
        $queue->save();
    }

    public function removeFromQueue(int $modelRecordId, ?string $modelClass = null): void
    {
        $modelId = $this->resolveModelId($modelClass);
        $codeQueueIdList = D3CodesCodeQueue::find()
            ->select('d3codes_code_queue.id')
            ->innerJoin(
                'd3codes_code_record',
                'd3codes_code_record.id = d3codes_code_queue.record_id'
            )
            ->andWhere([
                'd3codes_code_record.code_id' => $this->codeId,
                'd3codes_code_record.model_id' => $modelId,
                'd3codes_code_record.model_record_id' => $modelRecordId
            ])
            ->column();
        D3CodesCodeQueue::deleteAll(['id' => $codeQueueIdList]);

    }

    public function countQueue(int $modelRecordId, ?string $modelClass = null): int
    {
        $modelId = $this->resolveModelId($modelClass);
        return (int)D3CodesCodeRecord::find()
            ->select(['cnt'=>'IFNULL(COUNT(*),0)'])
            ->innerJoin(
                'd3codes_code_queue',
                'd3codes_code_record.id = d3codes_code_queue.record_id'
            )
            ->andWhere([
                'd3codes_code_record.code_id' => $this->codeId,
                'd3codes_code_record.model_id' => $modelId,
                'd3codes_code_record.model_record_id' => $modelRecordId
            ])
            ->scalar();
    }

    /**
     * @param int $modelRecordId
     * @param string $code
     * @return bool|string
     * @throws D3ActiveRecordException
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function createNewRecord(int $modelRecordId, string $code = '', ?string $modelClass = null)
    {
        $step = 1;
        $modelId = $this->resolveModelId($modelClass);
        foreach ($this->seriesList as $series){
            if ($code) {
                if (!$number = $series->getCodeNumber($code)) {
                    continue;
                }
            } else {
                if (!$number = $this->createNextCode($series, $step, $modelClass)) {
                    continue;
                }
            }

            $model = new D3CodesCodeRecord();
            $model->code_id = $this->codeId;
            $model->model_id = $modelId;
            $model->series_id = $series->getSerriesId();
            $model->model_record_id = $modelRecordId;
            $i = 1;
            while ($i < 10) {
                $model->full_code = $series->createCode($number);
                $model->sqn = $number;
                if (!$transaction = Yii::$app->db->beginTransaction()) {
                    throw new Exception('Can not initiate transaction');
                }
                try {
                    if (!$model->save()) {
                        throw new D3ActiveRecordException($model);
                    }
                    $transaction->commit();
                    return $model->full_code;
                } catch (IntegrityException $e) {
                    $transaction->rollBack();
                    usleep(random_int(100, 400000));
                    if (strpos($e->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') !== false) {
                        $step ++;
                        $i ++;
                        continue;
                    }
                    Yii::error([
                        'msg' => 'Try create code: ' . $e->getMessage(),
                        'exception' => $e
                    ]);
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    usleep(random_int(100, 400000));
                       Yii::error([
                        'msg' => 'Try create code: ' . $e->getMessage(),
                        'exception' => $e
                    ]);
                }
                $i ++;
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
    public function createNextCode(Serries $series, int $step = 1, ?string $modelClass = null)
    {
        $seriesId = $series->getSerriesId();
        $modelId = $this->resolveModelId($modelClass);
        $maxSqn = D3CodesCodeRecord::find()
            ->select('MAX(sqn)')
            ->where([
                'code_id' => $this->codeId,
                'model_id' => $modelId,
                'series_id' => $seriesId
            ])
            ->scalar();
        return $series->getNextSqn($maxSqn, $step);
    }

    /**
     * @param string|null $modelClass
     * @return int|null
     */
    private function resolveModelId(?string $modelClass = null): ?int
    {
        if ($modelClass !== null) {
            return SysModelsDictionary::getIdByClassName($modelClass);
        }
        return $this->modelId;
    }

}
