<?php

namespace d3yii2\d3codes\dictionaries;

use Yii;
use d3yii2\d3codes\models\D3CodesSeries;
use yii\helpers\ArrayHelper;

class D3CodesSerriesDictionary{

    private const CACHE_KEY_LIST = 'D3CodesSerriesDictionaryList';

    public static function getIdByName(string $name): int
    {
        $list = self::getList();
        return (int)array_search($name, $list, true);
    }

    public static function getList(): array
    {
        return Yii::$app->cache->getOrSet(
            self::CACHE_KEY_LIST,
            static function () {
                return ArrayHelper::map(
                    D3CodesSeries::find()
                    ->select([
                        'id' => 'id',
                        'name' => 'name',
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
