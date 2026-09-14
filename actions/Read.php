<?php


namespace d3yii2\d3codes\actions;


use d3system\helpers\FlashHelper;
use d3yii2\d3codes\models\CodeReader;
use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\web\Response;

class Read extends Action
{

    public string $codeReaderModelClass = CodeReader::class;
    /**
     * @var array define redirect url for models classes
     *   example
     *  [
     *     [
     *        'modelClass' => 'store/models/Box'
     *        'url' => [
     *              '/store/box/view',
     *              'id' => 'id'
     *        ],
     *        'modelClass' => 'store/models/Pallet'
     *        'url' => srartic function(store/models/Pallet $model){
     *              return [
     *                 'store/pallet',
     *                  'palletId' => $model->id
     *                  'type' => 'reader'
     *              ]
     *         },
     *     ]
     *  ]
     */
    public array $modelRedirectUrlList = [];

    /**
     * @var string|null
     */
    public ?string $componentCodeReaderName = null;

    /**
     * @var string|null  view file
     */
    public ?string $view = null;

    public $renderParams;

    /**
     * @return string|Response
     * @throws Exception
     */
    public function run()
    {
        $request = Yii::$app->request;
        $codeReaderModelClass = $this->codeReaderModelClass;
        $readerModel = new $codeReaderModelClass();
        $readerModel->componentCodeReaderName = $this->componentCodeReaderName;
        try {
            if ($readerModel->load($request->post())
                && $readerModel->validate()
                && $this->modelRedirectUrlList
            ) {
                $modelClass = get_class($readerModel->model);
                $primaryKey = $readerModel->model::primaryKey()[0];
                $primaryKeyValue = $readerModel->model->getPrimaryKey();
                $url = false;
                foreach ($this->modelRedirectUrlList as $modelRedirectUrl) {
                    if ($modelClass === $modelRedirectUrl['modelClass']) {
                        $redirectUrl = $modelRedirectUrl['url'];
                        if (is_callable($redirectUrl)) {
                            $url = $redirectUrl($readerModel->model, $request);
                            break;
                        }
                        foreach ($redirectUrl as $key => $value) {
                            if ($key === $primaryKey) {
                                $redirectUrl[$key] = $primaryKeyValue;
                                $url = $redirectUrl;
                                break;
                            }
                        }
                    }
                }
                if (!$url) {
                    throw new Exception('Can not create URL for model');
                }
                $this->controller->redirect($url);
            }
        } catch (Exception $e) {
            FlashHelper::processException($e);
        }

        if($this->renderParams && is_callable($this->renderParams)){
            $renderParams = ($this->renderParams)($request, $readerModel);
        }else{
            $renderParams = [
                'model' => $readerModel
            ];
        }
        return $this->controller->render($this->view, $renderParams);
    }
}