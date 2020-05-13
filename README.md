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

### Controller and Form
Controller
```php
use d3yii2\d3codes\models\CodeReader;

        $codeReaderModel = new CodeReader();
        $codeReaderModel->componentCodeReaderName = 'codeReader';
        $post = Yii::$app->request->post();
        if($post && $codeReaderModel->load(Yii::$app->request->post())){
                    /** @var CwpalletPallet $palletModel */
                    $palletModel = $codeReaderModel->model;
        }
        if ($codeReaderModel->hasErrors()) {
            FlashHelper::modelErrorSummary($codeReaderModel);
        }        
        return $this->render('manufacturing', [
            'packList' =>  $searchModel
                ->manufacturedPacks(true)
                ->all(),
            'packId' => $packId,
            'codeReaderModel' => $codeReaderModel
        ]);        
```
form
```php
                $form = ActiveForm::begin([
                    'id' => 'BauncerCodeReading',
                    'enableClientValidation' => false,
                    'errorSummaryCssClass' => 'error-summary alert alert-error',
                    'fieldConfig' => [
                        'template' => "{label}\n{beginWrapper}\n{input}\n{error}\n{endWrapper}",
                    ],

                ]);
                echo $form
                    ->field(
                        $codeReaderModel,
                        'code',
                        [
                            'inputOptions' => [
                                'autofocus' => 'autofocus',
                                'class' => 'form-control',
                                'tabindex' => '1'
                            ]
                        ])
                    ->textInput()
                    ->label('');


                echo ThButton::widget([
                    'label' => 'Process',
                    'id' => 'saveCode',
                    'icon' => ThButton::ICON_CHECK,
                    'type' => ThButton::TYPE_SUCCESS,
                    'submit' => true,
                    'htmlOptions' => [
                        'name' => 'action',
                        'value' => 'save',
                    ],
                ]);
                ActiveForm::end();
```

## Examples
