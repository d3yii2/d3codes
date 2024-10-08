<?php


namespace d3yii2\d3codes\models;


use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;

class CodeReader extends Model
{

    /** @var string */
    public ?string $code = null;

    /** @var ActiveRecord  */
    public $model;

    /** @var string */
    public $componentCodeReaderName;

    public function rules(): array
    {
        return [
            ['code','required'],
            ['code','string'],
        ];
    }

    /**
     * @param array $data
     * @param null $formName
     * @return bool
     * @throws InvalidConfigException
     */
    public function load($data, $formName = null): bool
    {
        if(!parent::load($data, $formName)){
            return false;
        }

        $this->code = trim($this->code);

        /** Check no ASCII chars  */
        $codeSanitised = preg_replace('/[[:^print:]]/', '', $this->code);
        if ($codeSanitised !== $this->code) {
            $this->addError('code','The barcode was read incorrectly');
            return false;
        }

        /** @var  \d3yii2\d3codes\components\CodeReader $codeReader */
        $codeReader = Yii::$app->get($this->componentCodeReaderName);
        if(strlen($this->code) && !$this->model = $codeReader->findModel($this->code)) {
            $this->addError('code','Code no found');
            return false;
        }
        return true;
    }
}
