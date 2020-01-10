#Codes"

## Features


## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ composer require d3yii2/d3codes "*"
```

or add

```
"d3yii2/d3codes": "*"
```

to the `require` section of your `composer.json` file.


## Configuration
```php
 'components' => [
        'palletCodeRecorder' => [
            'class' => 'd3yii2\d3codes\components\CodeRecorder',
            'codeName' => 'pallets bar code',
            'series' => [
                'p01' => [
                    'prefix' => 'p01',
                    'length' => 5,
                    'from' => 1,
                    'to' => 20000
                ]
            ],
            'modelClass' => 'wood\clasifiers\models\Pallet',
            'componentsSysModel' => 'sysModel'
        ],
        'codeReader' => [
            'class' => 'd3yii2\d3codes\components\CodeReader',
            'modelClassList' => [
                'wood\clasifiers\models\Pallet'
            ],
            'componentsSysModel' => 'sysModel'
        ],
        'sysModel' => [
            'class' => 'd3system\compnents\ModelsList'
        ],
    ]
        
```

## Usage
```php

    $barCode = \Yii::$app->palletCodeRecorder->createNewRecord($palletModel->id);

    $palletModel = \Yii::$app->palletCodeRecorder->codeReader($barcodeReadedByBarCodeScaner);       

```
## Examples
