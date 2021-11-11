<?php

use yii\db\Migration;

class m211110_120707_add_code_record_add_unique_codeId_sqn  extends Migration {

    public function safeUp() { 
        $this->execute('
            ALTER TABLE `d3codes_code_record`
              ADD UNIQUE INDEX (`code_id`, `sqn`);
            
                    
        ');
    }

    public function safeDown() {
        echo "m211110_120707_add_code_record_add_unique_codeId_sqn cannot be reverted.\n";
        return false;
    }
}
