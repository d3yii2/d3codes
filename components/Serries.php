<?php


namespace d3yii2\d3codes\components;


use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3codes\dictionaries\D3CodesSerriesDictionary;
use d3yii2\d3codes\models\D3CodesSeries;

class Serries
{
    /** @var string */
    private $name;

    /** @var string */
    private $prefix;

    /** @var int */
    private $length;

    /** @var int */
    private $from;

    /** @var int */
    private $to;

    /**
     * Serries constructor.
     * @param string $name
     * @param string $prefix
     * @param int $length
     * @param int $from
     * @param int $to
     */
    public function __construct(string $name, string $prefix, int $length, int $from, int $to)
    {
        $this->name = $name;
        $this->prefix = $prefix;
        $this->length = $length;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return int
     * @throws D3ActiveRecordException
     */
    public function getSerriesId(): int
    {
        if($id = D3CodesSerriesDictionary::getIdByName($this->name)){
            return $id;
        }
            $model = new D3CodesSeries();
            $model->name = $this->name;
            if(!$model->save()){
                throw new D3ActiveRecordException($model);
            }
            return (int)$model->id;
    }

    /**
     * @param false|int $lastSqn
     * @return bool|int
     */
    public function getNextSqn($lastSqn){
        if($lastSqn === false){
            return $this->from;
        }
        if((int)$lastSqn < $this->to){
            return (int)$lastSqn + 1;
        }

        return false;
    }

    public function createCode(int $sqn): string
    {
        return $this->prefix . str_pad($sqn, $this->length, '0', STR_PAD_LEFT);
    }

}