<?php


namespace d3yii2\d3codes\actions;


use cornernote\returnurl\ReturnUrl;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3codes\components\CodeRecorder;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;

class PrintCode extends Action
{

    /** @var string|\Closure layout for html or closure   */
    public $layout;

    /**
     * @var string
     */
    public $componentRecorderName;
    /**
     * @var string  view file
     */
    public $view;

    public $data;

    /**
     * @param int $id
     * @return string
     * @throws D3ActiveRecordException
     * @throws InvalidConfigException
     */
    public function run(int $id)
    {
        if ($this->layout instanceof \Closure) {
            call_user_func($this->layout, $id);
            return $this->controller->redirect(['view', 'id' => $id]);
        }
        if ($this->layout) {
            $this->controller->layout = $this->layout;
        } else {
            $this->controller->layout = '@vendor/d3yii2/d3codes/views/layouts/label';
        }
        /** @var CodeRecorder $codeRecorder */
        $codeRecorder = Yii::$app->get($this->componentRecorderName);
        $code = $codeRecorder->getCodeOrCreate($id);
        $data = $this->data;
        if (is_callable($data)) {
            $data = $data($id);
        }
        $data['code'] = $code;
        return $this->controller->render($this->view, $data);

    }

}