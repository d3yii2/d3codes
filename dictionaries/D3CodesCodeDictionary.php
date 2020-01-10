<?php

namespace d3yii2\d3codes\dictionaries;

use d3system\exceptions\D3ActiveRecordException;
use Yii;
use d3yii2\d3codes\models\D3CodesCode;
use yii\helpers\ArrayHelper;

class D3CodesCodeDictionary{

    private const CACHE_KEY_LIST = 'D3CodesCodeDictionaryList';

    /**
     * @param string $name
     * @return int
     * @throws D3ActiveRecordException
     */
    public static function getIdByName(string $name): int
    {
        $list = self::getList();
        if($id = (int)array_search($name, $list, true)){
            return $id;
        }
        $model = new D3CodesCode();
        $model->name = $name;
        if(!$model->save()){
            throw new D3ActiveRecordException($model);
        }

        return $model->id;

    }

    public static function getList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_LIST,
            static function () {
                return ArrayHelper::map(
                    D3CodesCode::find()
                    ->select([
                        'id' => 'id',
                        'name' => 'id',
                        //'name' => 'CONCAT(code,\' \',name)'
                    ])
                    ->orderBy([
                        'id' => SORT_ASC,
                    ])
                    ->asArray()
                    ->all()
                ,
                'id',
                'name'
                );
            }
        );
    }

    public static function clearCache(): void
    {
        Yii::$app->cache->delete(self::CACHE_KEY_LIST);
    }
}
