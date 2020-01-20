<?php


namespace d3yii2\d3codes\actions;


use d3yii2\d3codes\models\CodeReader;
use Yii;
use yii\base\Action;
use yii\base\Exception;

class Read extends Action
{

    public $codeReaderModelClass = CodeReader::class;
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
    public $modelRedirectUrlList = [];

    /**
     * @var string
     */
    public $componentCodeReaderName;

    /**
     * @var string  view file
     */
    public $view;

    public $renderParams;
    /**
     * @throws Exception
     */
    public function run()
    {
        $request = Yii::$app->request;
        $codeReaderModelClass = $this->codeReaderModelClass;
        $readerModel = new $codeReaderModelClass();
        $readerModel->componentCodeReaderName = $this->componentCodeReaderName;
        if ($readerModel->load($request->post()) && $readerModel->validate()){
           if($this->modelRedirectUrlList) {
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
               if(!$url){
                   throw new Exception('Can not create URL for model');
               }
               $this->controller->redirect($url);
           }
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