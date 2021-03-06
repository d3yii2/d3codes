<?php
// This class was automatically generated by a giiant build task
// You should not change it manually as it will be overwritten on next build

namespace d3yii2\d3codes\models\base;


/**
 * This is the base-model class for table "d3codes_code_queue".
 *
 * @property integer $id
 * @property integer $record_id
 *
 * @property \d3yii2\d3codes\models\D3codesCodeRecord $record
 * @property string $aliasModel
 */
abstract class D3CodesCodeQueue extends \yii\db\ActiveRecord
{



    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return 'd3codes_code_queue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'integer Unsigned' => [['id','record_id'],'integer' ,'min' => 0 ,'max' => 4294967295],
            [['record_id'], 'required'],
            [['record_id'], 'exist', 'skipOnError' => true, 'targetClass' => \d3yii2\d3codes\models\D3codesCodeRecord::className(), 'targetAttribute' => ['record_id' => 'id']]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'record_id' => 'Record ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecord()
    {
        return $this->hasOne(\d3yii2\d3codes\models\D3codesCodeRecord::className(), ['id' => 'record_id'])->inverseOf('d3codesCodeQueues');
    }

}
