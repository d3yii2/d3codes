<?php

use yii\db\Migration;

class m200824_180707_d3codes_code_record_add_index  extends Migration {

    public function safeUp() {

        $this->execute('
            ALTER TABLE `d3codes_code_record`   
              ADD  INDEX `D3CodesCodeRecord_ModelrecordIdModelId` (`model_record_id`, `model_id`);

        ');

    }

    public function safeDown() {
        echo "m200824_180707_d3codes_code_record_add_index cannot be reverted.\n";
        return false;
    }
}
