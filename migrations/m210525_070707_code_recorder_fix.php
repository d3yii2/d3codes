<?php

use yii\db\Migration;

class m210525_070707_code_recorder_fix  extends Migration {

    public function safeUp() { 
        $this->execute('
            delete from `d3codes_code_queue`;
        ');
        $this->execute('
            ALTER TABLE `d3codes_code_queue`   
              CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
                    
        ');
    }

    public function safeDown() {
        echo "m210525_070707_code_recorder_fix cannot be reverted.\n";
        return false;
    }
}
