## Features

Created as Yii2 moule. Actualy generate barccodes, bot can easy implement Qrcodes
Functionality
 - define code series by prefix and interval from and to 
 - creating codes from series and assigne to active record
 - read codes and assign to active record
 - label layouts
 - label printing form windows server

## DB Schema
![DB Schema](/doc/DbSchema.png)

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
### recorder and Readers
Recorder generate new barcodes from defined series. For diferrent types define different components. 
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
    ]
        
```
### Printer
For printing direct from the Windows server.
Use Chrome for converting to PDF and for sending to printer use   PDFtoPrinter http://www.columbia.edu/~em36/pdftoprinter.html

To composer add repository https://github.com/uldisn/php-exec.git 
```json
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/uldisn/php-exec.git"
        },

```

```text
# Bar code printer
BOUNCER_PRINTER=BouncerPrinter
PDFtoPrinter=H:\yii2\cewood\PDFtoPrinter.exe

# chrome
CHROME_PATH=C:\Program Files (x86)\Google\Chrome\Application\chrome.exe
```

```php
 'components' => [
        'bouncerPrinter' => [
            'class' => '\d3yii2\d3codes\components\PrintWindowsPrinter',
            'printerName' => getenv('BOUNCER_PRINTER'),
            'chromeExe' => getenv('CHROME_PATH'),
            'PDFtoPrinter' => getenv('PDFtoPrinter'),
        ],
    ]
        
```


## Usage
### creating new barcode
```php

    $barCode = \Yii::$app->palletCodeRecorder->createNewRecord($palletModel->id);
    $palletModel = \Yii::$app->palletCodeRecorder->codeReader($barcodeReadedByBarCodeScaner);       

```

### adding code to model

```php
class BtlinePp extends BaseBtlinePp
{

    public function rules()
    {
        return array_merge(parent::rules(),[
            ['code','safe']
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'code' => 'Code'
        ]);
    }

    /**
     * @return string
     * @throws D3ActiveRecordException
     */
    public function getCode(): string
    {
        return Yii::$app->ppCodeRecorder->getCodeOrCreate($this->id);
    }
}

```

###  Print barcode by printer
```php
use d3yii2\d3codes\actions\PrintCode;
class BatchController
{

    public function actions() {
        $OC = $this;
        return [
            'pp-print-barcode' => [
                'class' => PrintCode::class,
                'componentRecorderName' => 'ppCodeRecorder',
                'layout' => '@layout_barcode',
                'view' => 'print_barcode',
                'data' => static function(int $id) use ($OC){
                    return [
                        'model' => $OC->findModel($id),
                    ];
                }
            ],
        ];
    }
}
```

###  reading in Controller and Form

For form use model d3yii2\d3codes\models\CodeReader.


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

### Print from server

```php
        try {
            
            $url = Yii::$app->urlManager->createAbsoluteUrl([
                '/cwstore/pack/print-barcode',
                'id' => $id
            ]);
            if(Yii::$app->bouncerPrinter->print($url)){
                FlashHelper::addSuccess('Etiķete nosūtīta uz izsitēja printera');
            }else{
                FlashHelper::addDanger('Radās kļūda drukājot etiķeti');
            }
        } catch (Exception $e) {
            FlashHelper::processException($e);
        }


```
