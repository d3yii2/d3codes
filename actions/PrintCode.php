<?php


namespace d3yii2\d3codes\actions;

use Closure;
use d3system\exceptions\D3ActiveRecordException;
use d3yii2\d3codes\components\CodeRecorder;
use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\Response;

class PrintCode extends Action
{

    /** @var string|Closure layout for HTML or closure   */
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
     * @param array $params
     * @return string|Response
     * @throws D3ActiveRecordException
     * @throws InvalidConfigException
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function run(int $id, array $params = [])
    {
        if ($this->layout instanceof Closure) {
            call_user_func($this->layout, $id, $params);
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